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

        // Compute debt for each student
        $students = $students->map(function ($student) {
            $calc = $student->calculateDebtFromCreationUsingCurrentMonthly();
            $student->debt              = $calc['debt'];
            $student->monthly_amount    = $calc['monthly_amount'];
            $student->unpaid_periods    = $calc['unpaid_periods'];
            $student->selectable_periods = $calc['selectable_periods'] ?? [];
            $student->next_unpaid_period = $calc['next_unpaid_period'];
            return $student;
        });

        // Active announcements not yet read by this user (most recent first)
        $readIds = $user->readAnnouncements()->pluck('announcement_id');

        $announcements = Announcement::active()
            ->whereNotIn('id', $readIds)
            ->orderByDesc('created_at')
            ->get();

        $studentIds = $students->pluck('id')->all();
        $readNoteIds = $user->readStudentNotes()->pluck('student_class_note_id');

        $notes = StudentClassNote::query()
            ->with(['subject.subjectType', 'student', 'author'])
            ->whereIn('student_id', $studentIds)
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
        $readIds = $user->readStudentNotes()->pluck('student_class_note_id')->flip();

        $notes = StudentClassNote::query()
            ->with(['subject.subjectType', 'student', 'author'])
            ->whereIn('student_id', $studentIds)
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

        $canRead = $user->students()->where('students.id', $note->student_id)->exists();
        abort_unless($canRead, 403);

        $note->readers()->syncWithoutDetaching([$user->id => ['read_at' => now()]]);

        return redirect()->back();
    }
}