<?php

namespace App\Http\Controllers;

use App\Models\StudentClassNote;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherStudentNoteController extends Controller
{
    public function index(Subject $subject)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);

        $students = $subject->students()->orderBy('name')->get(['students.id', 'students.name', 'students.dni']);
        $notes = $subject->studentNotes()
            ->with(['student:id,name,dni', 'author:id,name'])
            ->latest()
            ->get();

        return view('portal.teacher.notes.index', compact('subject', 'students', 'notes'));
    }

    public function store(Request $request, Subject $subject)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string'],
        ]);

        $isEnrolled = $subject->students()->where('students.id', $data['student_id'])->exists();
        abort_unless($isEnrolled, 422, 'El alumno no pertenece a esta clase.');

        StudentClassNote::query()->create([
            'subject_id' => $subject->id,
            'student_id' => $data['student_id'],
            'teacher_user_id' =>  Auth::id(),
            'title' => $data['title'] ?? null,
            'body' => $data['body'],
        ]);

        return redirect()
            ->route('portal.teacher.notes.index', $subject)
            ->with('success', 'Nota cargada correctamente.');
    }

    public function edit(Subject $subject, StudentClassNote $note)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $this->ensureNoteBelongsToSubject($subject, $note);

        $students = $subject->students()->orderBy('name')->get(['students.id', 'students.name', 'students.dni']);

        return view('portal.teacher.notes.edit', compact('subject', 'note', 'students'));
    }

    public function update(Request $request, Subject $subject, StudentClassNote $note)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $this->ensureNoteBelongsToSubject($subject, $note);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string'],
        ]);

        $isEnrolled = $subject->students()->where('students.id', $data['student_id'])->exists();
        abort_unless($isEnrolled, 422, 'El alumno no pertenece a esta clase.');

        $note->update([
            'student_id' => $data['student_id'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'],
            'teacher_user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('portal.teacher.notes.index', $subject)
            ->with('success', 'Nota actualizada correctamente.');
    }

    public function destroy(Subject $subject, StudentClassNote $note)
    {
        $subject = $this->resolveSubjectForCurrentTeacher($subject->id);
        $this->ensureNoteBelongsToSubject($subject, $note);

        $note->delete();

        return redirect()
            ->route('portal.teacher.notes.index', $subject)
            ->with('success', 'Nota eliminada correctamente.');
    }

    protected function resolveSubjectForCurrentTeacher(int $subjectId): Subject
    {
        $user = Auth::user();
        $query = Subject::query()->with(['subjectType', 'titularTeacher', 'suplenteTeacher']);

         if (!$user instanceof \App\Models\User) {
    abort(403);
}

        if (! $user->isAdmin()) {
            abort_unless($user->teacher_id, 403, 'No tenés un profesor vinculado.');

            $query->where(function ($q) use ($user) {
                $q->where('titular_teacher_id', $user->teacher_id)
                    ->orWhere('suplente_teacher_id', $user->teacher_id);
            });
        }

        return $query->findOrFail($subjectId);
    }

    protected function ensureNoteBelongsToSubject(Subject $subject, StudentClassNote $note): void
    {
        abort_unless((int) $note->subject_id === (int) $subject->id, 404);
    }
}
