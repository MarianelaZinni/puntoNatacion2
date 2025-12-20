<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectType;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Página del calendario (ahora usa grilla semanal).
     */
    public function index()
    {
        $subjectTypes = SubjectType::all();

        // Colores fijos por materia (id => color)
        $subjectColors = [
            1 => '#29b1dc',
            2 => '#48bb78',
            3 => '#f59e42',
            4 => '#ed64a6',
            5 => '#a78bfa',
            6 => '#60a5fa',
            7 => '#f472b6',
            8 => '#f97316',
        ];

        // Cargamos las clases y preparamos un array serializable para inyectar en JS
        $subjects = Subject::with(['subjectType', 'students'])->get();

        $subjectsForJs = $subjects->map(function ($s) {
            return [
                'id' => $s->id,
                'subject_type_id' => $s->subject_type_id,
                'capacity' => $s->capacity,
                'day' => $s->day,
                'start_time' => substr($s->start_time, 0, 5),
                'end_time' => substr($s->end_time, 0, 5),
                'subject_type' => $s->subjectType ? [
                    'id' => $s->subjectType->id,
                    'value' => $s->subjectType->value ?? null,
                    'description' => $s->subjectType->description ?? null,
                    'has_teacher' => $s->subjectType->has_teacher ?? true,
                ] : null,
                'students' => $s->students->map(function ($st) {
                    return [
                        'id' => $st->id,
                        'name' => $st->name ?? $st->nombre ?? null,
                        'email' => $st->email ?? null,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        // Serializamos subjectTypes para JS sin closures (preparado en controlador)
        $subjectTypesForJs = $subjectTypes->map(function ($t) {
            return [
                'id' => $t->id,
                'label' => $t->description ?? $t->value ?? $t->name,
            ];
        })->values()->toArray();

        return view('subjects.index', compact('subjectTypes', 'subjectColors', 'subjectsForJs', 'subjectTypesForJs'));
    }

    /**
     * Devuelve los eventos en formato FullCalendar (mantenido para compatibilidad).
     */
    public function events()
    {
        // Eager load subjectType y students para evitar N+1
        $subjects = Subject::with(['subjectType', 'students'])->get();

        $events = [];
        $daysMap = [
            'Domingo'   => 0,
            'Lunes'     => 1,
            'Martes'    => 2,
            'Miercoles' => 3,
            'Jueves'    => 4,
            'Viernes'   => 5,
            'Sabado'    => 6,
        ];
        // Colores fijos por materia (id => color)
        $subjectColors = [
            1 => '#29b1dc',
            2 => '#48bb78',
            3 => '#f59e42',
            4 => '#ed64a6',
            5 => '#a78bfa',
        ];

        foreach ($subjects as $subject) {
            $dow = $daysMap[$subject->day] ?? 0;
            $color = $subjectColors[$subject->subject_type_id] ?? '#29b1dc';

            // Calcula cupo libre (cupo total - alumnos inscriptos)
            $enrolled = $subject->students ? $subject->students->count() : 0;
            $free_capacity = $subject->capacity - $enrolled;

            $baseTitle = optional($subject->subjectType)->description
                ?? optional($subject->subjectType)->value
                ?? 'Sin materia';

            $titleWithCapacity = sprintf('%s — Cupo: %d | Libre: %d', $baseTitle, $subject->capacity, $free_capacity);

            $events[] = [
                'id' => $subject->id,
                'title' => $titleWithCapacity,
                'daysOfWeek' => [$dow],
                'startTime' => substr($subject->start_time, 0, 5),
                'endTime' => substr($subject->end_time, 0, 5),
                'color' => $color,
                'extendedProps' => [
                    'capacity' => $subject->capacity,
                    'free_capacity' => $free_capacity,
                    'subject_type_id' => $subject->subject_type_id,
                    'day' => $subject->day,
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Crear clase
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_type_id' => 'required|exists:subject_types,id',
            'capacity' => 'required|integer|min:1',
            'day' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $subject = Subject::create($data);

        return response()->json(['success' => true, 'subject' => $subject]);
    }

    /**
     * Editar clase
     */
    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'subject_type_id' => 'required|exists:subject_types,id',
            'capacity' => 'required|integer|min:1',
            'day' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Verificar que el nuevo cupo no sea menor que la cantidad de alumnos ya inscriptos
        $enrolled = $subject->students()->count();
        if (isset($data['capacity']) && $data['capacity'] < $enrolled) {
            $message = "No se puede reducir el cupo por debajo de los inscriptos actuales ({$enrolled}).";

            // Si la petición es AJAX, devolvemos JSON con error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            // Si es una petición normal, redirigimos con error
            return redirect()->back()->withInput()->with('error', $message);
        }

        $subject->update($data);

        return response()->json(['success' => true, 'subject' => $subject]);
    }

    /**
     * Drag & drop: mover clase a otro día/hora
     */
    public function move(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'day' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $subject->update($data);

        return response()->json(['success' => true, 'subject' => $subject]);
    }

    /**
     * Borrar clase
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return response()->json(['success' => true]);
    }
}