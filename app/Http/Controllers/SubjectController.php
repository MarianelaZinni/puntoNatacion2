<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectType;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Página del calendario
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
        ];

        return view('subjects.index', compact('subjectTypes', 'subjectColors'));
    }

    /**
     * Devuelve los eventos en formato FullCalendar (recurrentes semanalmente)
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

            // Usa description de subjectType como base del título; fallback a value o 'Sin materia'
            $baseTitle = optional($subject->subjectType)->description
                ?? optional($subject->subjectType)->value
                ?? 'Sin materia';

            // Concatenamos cupos al título para que FullCalendar lo muestre directamente
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