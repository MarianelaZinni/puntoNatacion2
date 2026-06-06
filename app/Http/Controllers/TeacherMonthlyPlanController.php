<?php

namespace App\Http\Controllers;

use App\Models\ClassMonthlyPlan;
use App\Models\Subject;
use Illuminate\Http\Request;

class TeacherMonthlyPlanController extends Controller
{
    public function index(Subject $subject)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $plans = $subject->monthlyPlans()->with('author')->orderByDesc('month')->get();

        return view('portal.teacher.plans.index', [
            'subject' => $subject,
            'plans' => $plans,
            'canManage' => $this->canManageSubject($subject),
        ]);
    }

    public function create(Subject $subject)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $this->ensureCanManage($subject);

        return view('portal.teacher.plans.create', compact('subject'));
    }

    public function store(Request $request, Subject $subject)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $this->ensureCanManage($subject);

        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'content' => ['required', 'string'],
        ]);

        ClassMonthlyPlan::query()->updateOrCreate(
            [
                'subject_id' => $subject->id,
                'month' => $data['month'].'-01',
            ],
            [
                'content' => $data['content'],
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('portal.teacher.plans.index', $subject)
            ->with('success', 'Plan mensual guardado correctamente.');
    }

    public function edit(Subject $subject, ClassMonthlyPlan $plan)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $this->ensureCanManage($subject);
        $this->ensurePlanBelongsToSubject($subject, $plan);

        return view('portal.teacher.plans.edit', compact('subject', 'plan'));
    }

    public function update(Request $request, Subject $subject, ClassMonthlyPlan $plan)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $this->ensureCanManage($subject);
        $this->ensurePlanBelongsToSubject($subject, $plan);

        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'content' => ['required', 'string'],
        ]);

        $plan->update([
            'month' => $data['month'].'-01',
            'content' => $data['content'],
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('portal.teacher.plans.index', $subject)
            ->with('success', 'Plan mensual actualizado correctamente.');
    }

    public function destroy(Subject $subject, ClassMonthlyPlan $plan)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $this->ensureCanManage($subject);
        $this->ensurePlanBelongsToSubject($subject, $plan);

        $plan->delete();

        return redirect()
            ->route('portal.teacher.plans.index', $subject)
            ->with('success', 'Plan mensual eliminado correctamente.');
    }

    protected function resolveSubjectForCurrentTeacher(int $subjectId): Subject
    {
        $user = auth()->user();
        $query = Subject::query()->with(['subjectType', 'titularTeacher', 'suplenteTeacher']);

        if (! $user->isAdmin()) {
            abort_unless($user->teacher_id, 403, 'No tenés un profesor vinculado.');

            $query->where(function ($q) use ($user) {
                $q->where('titular_teacher_id', $user->teacher_id)
                    ->orWhere('suplente_teacher_id', $user->teacher_id);
            });
        }

        return $query->findOrFail($subjectId);
    }

    protected function canManageSubject(Subject $subject): bool
    {
        $user = auth()->user();

        return $user->isAdmin() || (int) $subject->titular_teacher_id === (int) $user->teacher_id;
    }

    protected function ensureCanManage(Subject $subject): void
    {
        abort_unless($this->canManageSubject($subject), 403, 'Solo el profesor titular puede gestionar este plan.');
    }

    protected function ensurePlanBelongsToSubject(Subject $subject, ClassMonthlyPlan $plan): void
    {
        abort_unless((int) $plan->subject_id === (int) $subject->id, 404);
    }
}

