<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentPause;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentPauseController extends Controller
{
    /**
     * List all pauses for the given student.
     */
    public function index(Student $student)
    {
        $pauses = $student->pauses()->orderBy('start_date', 'desc')->get();
        return view('students.pauses.index', compact('student', 'pauses'));
    }

    /**
     * Store a new pause period.
     */
    public function store(Request $request, Student $student)
    {
        $request->validate([
            'start_date' => 'required|date_format:Y-m',
            'reason'     => 'nullable|string|max:255',
        ]);

        $start = Carbon::createFromFormat('Y-m', $request->input('start_date'))->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $student->pauses()->create([
            'start_date' => $start->toDateString(),
            'end_date'   => $end->toDateString(),
            'reason'     => $request->input('reason'),
        ]);

        return redirect()->route('students.pauses.index', $student)
            ->with('success', 'Período de pausa creado correctamente.');
    }

    /**
     * Show the edit form for an existing pause.
     */
    public function edit(Student $student, StudentPause $pause)
    {
        abort_if($pause->student_id !== $student->id, 404);
        abort_unless($pause->isEditable(), 403, 'Este período de pausa ya finalizó y no puede modificarse.');

        return view('students.pauses.edit', compact('student', 'pause'));
    }

    /**
     * Update an existing pause.
     */
    public function update(Request $request, Student $student, StudentPause $pause)
    {
        abort_if($pause->student_id !== $student->id, 404);
        abort_unless($pause->isEditable(), 403, 'Este período de pausa ya finalizó y no puede modificarse.');

        $request->validate([
            'start_date' => 'required|date_format:Y-m',
            'reason'     => 'nullable|string|max:255',
        ]);

        $start = Carbon::createFromFormat('Y-m', $request->input('start_date'))->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $pause->update([
            'start_date' => $start->toDateString(),
            'end_date'   => $end->toDateString(),
            'reason'     => $request->input('reason'),
        ]);

        return redirect()->route('students.pauses.index', $student)
            ->with('success', 'Período de pausa actualizado correctamente.');
    }

    /**
     * Delete a pause period (only while editable).
     */
    public function destroy(Student $student, StudentPause $pause)
    {
        abort_if($pause->student_id !== $student->id, 404);
        abort_unless($pause->isEditable(), 403, 'Este período de pausa ya finalizó y no puede eliminarse.');

        $pause->delete();

        return redirect()->route('students.pauses.index', $student)
            ->with('success', 'Período de pausa eliminado correctamente.');
    }
}