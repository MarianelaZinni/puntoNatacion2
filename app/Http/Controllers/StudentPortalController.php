<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    /**
     * Show the student portal: display all students linked to the current user.
     */
    public function index()
    {
        $user = auth()->user();

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

        return view('portal.student', compact('students'));
    }
}
