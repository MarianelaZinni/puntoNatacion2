<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectPrice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con la grilla semanal de clases.
     */
    public function index()
    {
        $user = auth()->user();

        // Redirect role-specific users to their own portals
        if ($user && $user->isAlumno()) {
            return redirect()->route('portal.student');
        }
        if ($user && $user->isProfesor()) {
            return redirect()->route('portal.teacher');
        }

        // Eager load para evitar N+1 (subjectType y students)
        $subjects = Subject::with(['subjectType', 'students'])->get();

        // Colores fijos por materia (id => color) - sincronizar con SubjectController si cambia
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

        // Preparar array plano para inyectar en JS (evita closures en la vista)
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

        // Obtener precios por defecto (subject_type_id = null) para inyectar y mostrar
        $defaults = SubjectPrice::whereNull('subject_type_id')->get();
        $subjectPricesForJs = [
            'teacher' => [],
            'no_teacher' => [],
        ];
        foreach ($defaults as $row) {
            if ($row->has_teacher) {
                $subjectPricesForJs['teacher'][(int)$row->times_per_week] = (float)$row->price;
            } else {
                $subjectPricesForJs['no_teacher'][(int)$row->times_per_week] = (float)$row->price;
            }
        }

        return view('dashboard', [
            'subjectsForJs' => $subjectsForJs,
            'subjectColors' => $subjectColors,
            'subjectPricesForJs' => $subjectPricesForJs,
        ]);
    }
}