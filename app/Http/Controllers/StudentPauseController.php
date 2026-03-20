<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentPause;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentPauseController extends Controller
{
    /**
     * Show the list of pause periods for a student.
     */
    public function index(Request $request)
    {
        $studentId = $request->query('student_id');

        if (!$studentId) {
            return redirect()->route('students.index')
                ->with('error', 'Debe seleccionar un alumno.');
        }

        $student = Student::findOrFail($studentId);

        $pauses = StudentPause::where('student_id', $studentId)
            ->orderBy('pause_period', 'desc')
            ->get();

        return view('student_pauses.index', compact('student', 'pauses'));
    }

    /**
     * Store a new pause period for a student.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'   => 'required|exists:students,id',
            'pause_period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        try {
            $periodCarbon = Carbon::createFromFormat('Y-m', $data['pause_period'])->startOfMonth();
            $data['pause_period'] = $periodCarbon->toDateString();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Período inválido. Use formato YYYY-MM.');
        }

        // Check for duplicate
        $exists = StudentPause::where('student_id', $data['student_id'])
            ->where('pause_period', $data['pause_period'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()
                ->with('error', 'Ya existe una pausa registrada para ese período.');
        }

        StudentPause::create($data);

        return redirect()->route('student_pauses.index', ['student_id' => $data['student_id']])
            ->with('success', 'Período de pausa registrado correctamente.');
    }

    /**
     * Show the edit form for a pause period.
     * Only allowed if the pause period has not yet passed.
     */
    public function edit(StudentPause $studentPause)
    {
        if ($studentPause->isPast()) {
            return redirect()->route('student_pauses.index', ['student_id' => $studentPause->student_id])
                ->with('error', 'No se puede editar una pausa de un período ya pasado.');
        }

        $studentPause->load('student');
        $periodFormatted = Carbon::parse($studentPause->pause_period)->format('Y-m');

        return view('student_pauses.edit', compact('studentPause', 'periodFormatted'));
    }

    /**
     * Update an existing pause period.
     * Only allowed if the original pause period has not yet passed.
     */
    public function update(Request $request, StudentPause $studentPause)
    {
        if ($studentPause->isPast()) {
            return redirect()->route('student_pauses.index', ['student_id' => $studentPause->student_id])
                ->with('error', 'No se puede modificar una pausa de un período ya pasado.');
        }

        $data = $request->validate([
            'pause_period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        try {
            $periodCarbon = Carbon::createFromFormat('Y-m', $data['pause_period'])->startOfMonth();
            $data['pause_period'] = $periodCarbon->toDateString();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Período inválido. Use formato YYYY-MM.');
        }

        // Check for duplicate (excluding current record)
        $exists = StudentPause::where('student_id', $studentPause->student_id)
            ->where('pause_period', $data['pause_period'])
            ->where('id', '!=', $studentPause->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()
                ->with('error', 'Ya existe una pausa registrada para ese período.');
        }

        $studentPause->update($data);

        return redirect()->route('student_pauses.index', ['student_id' => $studentPause->student_id])
            ->with('success', 'Pausa actualizada correctamente.');
    }

    /**
     * Delete a pause period.
     * Only allowed if the pause period has not yet passed.
     */
    public function destroy(StudentPause $studentPause)
    {
        if ($studentPause->isPast()) {
            return redirect()->route('student_pauses.index', ['student_id' => $studentPause->student_id])
                ->with('error', 'No se puede eliminar una pausa de un período ya pasado.');
        }

        $studentId = $studentPause->student_id;
        $studentPause->delete();

        return redirect()->route('student_pauses.index', ['student_id' => $studentId])
            ->with('success', 'Pausa eliminada correctamente.');
    }
}