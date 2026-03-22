<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TeacherPortalController extends Controller
{
    /**
     * Show the teacher portal: the teacher's profile and assigned classes.
     */
    public function index()
    {
           /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $user->loadMissing([
            'teacher' => fn ($q) => $q->with([
                'titularSubjects'  => fn ($q) => $q->with('subjectType'),
                'suplenteSubjects' => fn ($q) => $q->with('subjectType'),
            ]),
        ]);

        $teacher = $user->teacher;

        return view('portal.teacher', compact('teacher'));
    }
}