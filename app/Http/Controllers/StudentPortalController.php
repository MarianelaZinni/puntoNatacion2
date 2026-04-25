<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    /**
     * Show the student portal: display all students linked to the current user.
     */
    public function index()
    {
         /** @var \App\Models\User|null $user */
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

        // Active announcements visible to students (most recent first)
        $announcements = Announcement::active()
            ->orderByDesc('created_at')
            ->get();

        return view('portal.student', compact('students', 'announcements'));
    }
}