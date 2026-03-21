<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    /**
     * Show the teacher portal: the teacher's profile and assigned classes.
     */
    public function index()
    {
        $user = auth()->user()->load([
            'teacher' => fn ($q) => $q->with([
                'titularSubjects'  => fn ($q) => $q->with('subjectType'),
                'suplenteSubjects' => fn ($q) => $q->with('subjectType'),
            ]),
        ]);

        $teacher = $user->teacher;

        return view('portal.teacher', compact('teacher'));
    }
}
