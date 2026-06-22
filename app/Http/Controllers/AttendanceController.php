<?php

namespace App\Http\Controllers;

use App\Models\AttendanceList;
use App\Models\AttendanceRecord;
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

        $sortBy    = in_array($request->query('sort'), ['id', 'date']) ? $request->query('sort') : 'date';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $sortColumn = 'attendance_lists.' . $sortBy;

        $recent = AttendanceList::withCount('records as total')
            ->withCount(['records as present_count' => fn ($q) => $q->where('present', true)])
            ->with('subject.subjectType')
            ->orderBy($sortColumn, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('attendance.index', compact('subjects', 'recent') + [
            'today'     => now()->toDateString(),
            'sortBy'    => $sortBy,
            'direction' => $direction,
        ]);
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

        // Load or create the attendance list for this class + date
        $list = AttendanceList::firstOrCreate([
            'subject_id' => $subject->id,
            'date'       => $date,
        ]);

        // Pre-create a record for every enrolled student (present = true by default).
        // This ensures total always equals the number of enrolled students,
        // even if the teacher hasn't clicked anything yet.
        $enrolledIds = $subject->students()->pluck('students.id');
        foreach ($enrolledIds as $studentId) {
            AttendanceRecord::firstOrCreate(
                ['attendance_list_id' => $list->id, 'student_id' => $studentId],
                ['present' => true]
            );
        }

        // Build present/absent map keyed by student_id
        $existing = $list->records()->pluck('present', 'student_id')->toArray();

        $alreadySaved = true;

        return view('attendance.take', compact('subject', 'date', 'existing', 'alreadySaved', 'list'));
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
            $list = AttendanceList::firstOrCreate([
                'subject_id' => (int) $request->subject_id,
                'date'       => $request->date,
            ]);

            AttendanceRecord::updateOrCreate(
                [
                    'attendance_list_id' => $list->id,
                    'student_id'         => (int) $request->student_id,
                ],
                [
                    'present' => (bool) $request->present,
                ]
            );

            return response()->json(['ok' => true, 'list_id' => $list->id]);
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
                $list = AttendanceList::firstOrCreate([
                    'subject_id' => $subjectId,
                    'date'       => $date,
                ]);

                foreach ($enrolledStudentIds as $studentId) {
                    AttendanceRecord::updateOrCreate(
                        [
                            'attendance_list_id' => $list->id,
                            'student_id'         => $studentId,
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

    /**
     * Delete an attendance list and all its records.
     * DELETE /attendance/{attendanceList}
     */
    public function destroy(AttendanceList $attendanceList)
    {
        try {
            $attendanceList->delete();

            return redirect()
                ->route('attendance.index')
                ->with('success', 'Lista de asistencia eliminada correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error eliminando lista de asistencia: ' . $e->getMessage(), [
                'attendance_list_id' => $attendanceList->id,
                'exception'          => $e,
            ]);
            return redirect()
                ->route('attendance.index')
                ->with('error', 'No se pudo eliminar la lista: ' . $e->getMessage());
        }
    }
}