<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Show the form to select a class and date, and list past attendance sessions.
     * GET /attendance
     */
    public function index(Request $request)
    {
        $subjects = Subject::with(['subjectType'])->orderBy('day')->orderBy('start_time')->get();

        // Recent attendance sessions (distinct subject + date combinations, last 30)
        $recent = Attendance::selectRaw('subject_id, date, COUNT(*) as total, SUM(present) as present_count')
            ->groupBy('subject_id', 'date')
            ->orderByDesc('date')
            ->orderBy('subject_id')
            ->with('subject.subjectType')
            ->limit(30)
            ->get();

       return view('attendance.index', compact('subjects', 'recent') + ['today' => now()->toDateString()]);
    }

    /**
     * Show the attendance sheet for a specific class and date.
     * GET /attendance/take?subject_id=X&date=YYYY-MM-DD
     */
    public function take(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'date'       => 'required|date',
        ]);

        $subject = Subject::with(['subjectType', 'students' => function ($q) {
            $q->orderBy('name');
        }])->findOrFail($request->subject_id);

        $date = $request->date;

        // Load existing attendance records for this class + date
        $existing = Attendance::where('subject_id', $subject->id)
            ->whereDate('date', $date)
            ->pluck('present', 'student_id')  // keyed by student_id
            ->toArray();

        $alreadySaved = Attendance::where('subject_id', $subject->id)
            ->whereDate('date', $date)
            ->exists();

        return view('attendance.take', compact('subject', 'date', 'existing', 'alreadySaved'));
    }

    /**
     * Save (create or update) a single student's attendance record via AJAX.
     * POST /attendance/store-single
     */
    public function storeSingle(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:students,id',
            'date'       => 'required|date_format:Y-m-d',
            'present'    => 'required|boolean',
        ]);

        try {
            Attendance::updateOrCreate(
                [
                    'subject_id' => (int) $request->subject_id,
                    'student_id' => (int) $request->student_id,
                    'date'       => $request->date,
                ],
                [
                    'present' => (bool) $request->present,
                ]
            );

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::error('Error guardando asistencia individual: ' . $e->getMessage(), [
                'subject_id' => $request->subject_id,
                'student_id' => $request->student_id,
                'date'       => $request->date,
                'exception'  => $e,
            ]);
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Save (create or update) attendance for a class on a given date.
     * POST /attendance/store
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'date'       => 'required|date_format:Y-m-d',
        ]);

        $subjectId = (int) $request->subject_id;
        $date      = $request->date;

        // present_students is an array of student_ids that were checked as present
        $presentIds = $request->input('present_students', []);
        if (!is_array($presentIds)) {
            $presentIds = [];
        }
        $presentIds = array_map('intval', $presentIds);

        $subject = Subject::with('students')->findOrFail($subjectId);

        $enrolledStudentIds = $subject->students->pluck('id')->toArray();

        if (empty($enrolledStudentIds)) {
            Log::warning('Intento de guardar asistencia en clase sin alumnos inscriptos', [
                'subject_id' => $subjectId,
                'date'       => $date,
            ]);
            return redirect()
                ->route('attendance.take', ['subject_id' => $subjectId, 'date' => $date])
                ->with('error', 'Esta clase no tiene alumnos inscriptos. No se guardó ningún registro.');
        }

        try {
            DB::transaction(function () use ($enrolledStudentIds, $subjectId, $date, $presentIds) {
                foreach ($enrolledStudentIds as $studentId) {
                    Attendance::updateOrCreate(
                        [
                            'subject_id' => $subjectId,
                            'student_id' => $studentId,
                            'date'       => $date,
                        ],
                        [
                            'present' => in_array($studentId, $presentIds),
                        ]
                    );
                }
            });

            return redirect()
                ->route('attendance.take', ['subject_id' => $subjectId, 'date' => $date])
                ->with('success', 'Asistencia guardada correctamente.');

        } catch (\Throwable $e) {
            Log::error('Error guardando asistencia: ' . $e->getMessage(), [
                'subject_id' => $subjectId,
                'date'       => $date,
                'exception'  => $e,
            ]);
            return redirect()
                ->route('attendance.take', ['subject_id' => $subjectId, 'date' => $date])
                ->with('error', 'Ocurrió un error al guardar la asistencia: ' . $e->getMessage());
        }
    }
}