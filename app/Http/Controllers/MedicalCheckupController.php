<?php

namespace App\Http\Controllers;

use App\Models\MedicalCheckup;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MedicalCheckupController extends Controller
{
    /**
     * Muestra el listado de revisiones médicas de un alumno.
     */
    public function index(Request $request)
    {
        $studentId = $request->query('student_id');
        
        if (!$studentId) {
            return redirect()->route('students.index')
                ->with('error', 'Debe seleccionar un alumno.');
        }
        
        $student = Student::findOrFail($studentId);
        
        // Cargar revisiones médicas del alumno, ordenadas por fecha de revisión descendente
        $checkups = MedicalCheckup::where('student_id', $studentId)
            ->orderBy('checkup_date', 'desc')
            ->paginate(15);
        
        return view('medical_checkups.index', compact('student', 'checkups'));
    }

    /**
     * Muestra el formulario para crear una nueva revisión médica.
     */
    public function create(Request $request)
    {
        $studentId = $request->query('student_id');
        
        if (!$studentId) {
            return redirect()->route('students.index')
                ->with('error', 'Debe seleccionar un alumno.');
        }
        
        $student = Student::findOrFail($studentId);
        
        return view('medical_checkups.create', compact('student'));
    }

    /**
     * Guarda una nueva revisión médica.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'checkup_date' => 'required|date',
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'], // Formato YYYY-MM
            'approved' => 'required|boolean',
            'observations' => 'nullable|string|max:2000',
        ]);
        
        // Convertir period de YYYY-MM a YYYY-MM-01 para guardar como date
        try {
            $periodCarbon = Carbon::createFromFormat('Y-m', $data['period'])->startOfMonth();
            $data['period'] = $periodCarbon->toDateString();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Período inválido. Use formato YYYY-MM.');
        }
        
        MedicalCheckup::create($data);
        
        return redirect()->route('medical_checkups.index', ['student_id' => $data['student_id']])
            ->with('success', 'Revisión médica registrada correctamente.');
    }

    /**
     * Muestra el formulario para editar una revisión médica.
     */
    public function edit(MedicalCheckup $medicalCheckup)
    {
        $medicalCheckup->load('student');
        
        // Formatear period para el input type="month" (YYYY-MM)
        $periodFormatted = null;
        if ($medicalCheckup->period) {
            try {
                $periodFormatted = Carbon::parse($medicalCheckup->period)->format('Y-m');
            } catch (\Throwable $e) {
                $periodFormatted = Carbon::now()->format('Y-m');
            }
        } else {
            $periodFormatted = Carbon::now()->format('Y-m');
        }
        
        return view('medical_checkups.edit', compact('medicalCheckup', 'periodFormatted'));
    }

    /**
     * Actualiza una revisión médica existente.
     */
    public function update(Request $request, MedicalCheckup $medicalCheckup)
    {
        $data = $request->validate([
            'checkup_date' => 'required|date',
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'], // Formato YYYY-MM
            'approved' => 'required|boolean',
            'observations' => 'nullable|string|max:2000',
        ]);
        
        // Convertir period de YYYY-MM a YYYY-MM-01 para guardar como date
        try {
            $periodCarbon = Carbon::createFromFormat('Y-m', $data['period'])->startOfMonth();
            $data['period'] = $periodCarbon->toDateString();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Período inválido. Use formato YYYY-MM.');
        }
        
        $medicalCheckup->update($data);
        
        return redirect()->route('medical_checkups.index', ['student_id' => $medicalCheckup->student_id])
            ->with('success', 'Revisión médica actualizada correctamente.');
    }

    /**
     * Elimina una revisión médica.
     */
    public function destroy(MedicalCheckup $medicalCheckup)
    {
        $studentId = $medicalCheckup->student_id;
        $medicalCheckup->delete();
        
        return redirect()->route('medical_checkups.index', ['student_id' => $studentId])
            ->with('success', 'Revisión médica eliminada correctamente.');
    }
}