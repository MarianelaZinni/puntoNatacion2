<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\StudentClassNote;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    /**
     * Show the student portal: display all students linked to the current user.
     * Only announcements NOT yet read by this user are shown.
     */
   /**
     * Show the student portal: display all students linked to the current user.
     * Only announcements NOT yet read by this user are shown.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $students = $user->students()
            ->with([
                'subjects' => fn ($q) => $q->with('subjectType')->withCount('students')->orderBy('start_time'),
                'payments' => fn ($q) => $q->with('paymentMethod')->orderByDesc('payment_date'),
                'pauses',
            ])
            ->get();

        // Compute debt and payment_status for each student
        $students = $students->map(function ($student) {
            $calc = $student->calculateDebtFromCreationUsingCurrentMonthly();
            $student->debt               = $calc['debt'];
            $student->monthly_amount     = $calc['monthly_amount'];
            $student->unpaid_periods     = $calc['unpaid_periods'];
            $student->selectable_periods = $calc['selectable_periods'] ?? [];
            $student->next_unpaid_period = $calc['next_unpaid_period'];

            // Estado unificado desde el modelo (max created_at/active_from/DEBT_START_DATE; descuenta pausas)
            $student->payment_status = $student->debtStatus();

            return $student;
        });

        // Active announcements not yet read by this user (most recent first)
        $readIds = $user->readAnnouncements()->pluck('announcement_id');

        $announcements = Announcement::active()
            ->whereNotIn('id', $readIds)
            ->orderByDesc('created_at')
            ->get();

        $studentIds = $students->pluck('id')->all();
        $subjectIds = $students->flatMap(fn($s) => $s->subjects->pluck('id'))->unique()->values()->all();
        $readNoteIds = $user->readStudentNotes()->pluck('student_class_note_id');

        $notes = StudentClassNote::query()
            ->with(['subject.subjectType', 'student', 'author'])
            ->where(function ($q) use ($studentIds, $subjectIds) {
                // Notes for a specific student of this user
                $q->whereIn('student_id', $studentIds)
                  // OR group notes for any class the user's students attend
                  ->orWhere(function ($q2) use ($subjectIds) {
                      $q2->whereNull('student_id')
                         ->whereIn('subject_id', $subjectIds);
                  });
            })
            ->whereNotIn('id', $readNoteIds)
            ->latest()
            ->limit(8)
            ->get();

        return view('portal.student', compact('students', 'announcements', 'notes'));
    }

    /**
     * Mark an announcement as read for the current user and redirect back.
     */
    public function markRead(Announcement $announcement)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Use syncWithoutDetaching to avoid duplicate-key errors
        $announcement->readers()->syncWithoutDetaching([$user->id => ['read_at' => now()]]);

        return redirect()->back();
    }

    /**
     * Show the full list of active announcements with read/unread indicator.
     */
    public function announcements()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $readIds = $user->readAnnouncements()->pluck('announcement_id')->flip();

        $announcements = Announcement::active()
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($a) use ($readIds) {
                $a->is_read = $readIds->has($a->id);
                return $a;
            });

        return view('portal.announcements', compact('announcements'));
    }

    public function notes()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $studentIds = $user->students()->pluck('students.id');
        $subjectIds = $user->students()
            ->with('subjects')
            ->get()
            ->flatMap(fn($s) => $s->subjects->pluck('id'))
            ->unique()
            ->values();
        $readIds = $user->readStudentNotes()->pluck('student_class_note_id')->flip();

        $notes = StudentClassNote::query()
            ->with(['subject.subjectType', 'student', 'author'])
            ->where(function ($q) use ($studentIds, $subjectIds) {
                $q->whereIn('student_id', $studentIds)
                  ->orWhere(function ($q2) use ($subjectIds) {
                      $q2->whereNull('student_id')
                         ->whereIn('subject_id', $subjectIds);
                  });
            })
            ->latest()
            ->get()
            ->map(function ($note) use ($readIds) {
                $note->is_read = $readIds->has($note->id);
                return $note;
            });

        return view('portal.notes', compact('notes'));
    }

    public function markNoteRead(StudentClassNote $note)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Allow reading if: note targets one of this user's students,
        // OR it's a group note for a class one of their students attends.
        if ($note->student_id !== null) {
            $canRead = $user->students()->where('students.id', $note->student_id)->exists();
        } else {
            $subjectIds = $user->students()
                ->with('subjects')
                ->get()
                ->flatMap(fn($s) => $s->subjects->pluck('id'))
                ->unique();
            $canRead = $subjectIds->contains($note->subject_id);
        }
        abort_unless($canRead, 403);

        $note->readers()->syncWithoutDetaching([$user->id => ['read_at' => now()]]);

        return redirect()->back();
    }
}