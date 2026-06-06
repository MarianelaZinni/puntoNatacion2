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
                'titularSubjects'  => fn ($q) => $q->with('subjectType')->withCount('students'),
                'suplenteSubjects' => fn ($q) => $q->with('subjectType')->withCount('students'),
            ]),
        ]);

        $teacher = $user->teacher;
        $calendarSubjects = collect();

        if ($teacher) {
            $calendarSubjects = $teacher->titularSubjects
                ->concat($teacher->suplenteSubjects)
                ->sortBy([
                    fn ($s) => $this->dayOrder($s->day),
                    fn ($s) => $s->start_time,
                ])
                ->values();
        }

        return view('portal.teacher', compact('teacher', 'calendarSubjects'));
    }

    protected function dayOrder(?string $day): int
    {
        return match ($day) {
            'Lunes' => 1,
            'Martes' => 2,
            'Miercoles' => 3,
            'Jueves' => 4,
            'Viernes' => 5,
            'Sabado' => 6,
            'Domingo' => 7,
            default => 99,
        };
    }
}