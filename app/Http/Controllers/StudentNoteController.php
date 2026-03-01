<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentNote;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentNoteController extends Controller
{
    /**
     * Guardar una nueva nota de un alumno
     */
    public function store(Request $request, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'note' => 'required|string|max:2000',
        ], [
            'teacher_id.required' => 'El profesor es obligatorio.',
            'teacher_id.exists' => 'El profesor no existe.',
            'subject_id.required' => 'La clase es obligatoria.',
            'subject_id.exists' => 'La clase no existe.',
            'note.required' => 'La nota es obligatoria.',
            'note.max' => 'La nota no puede superar 2000 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $teacher = Teacher::findOrFail($request->teacher_id);
        $subject = Subject::findOrFail($request->subject_id);

        // Verificar que el profesor puede agregar notas a esta clase
        if (!$teacher->canCommentOnSubject($subject)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para agregar notas en esta clase.'
            ], 403);
        }

        // Verificar que el alumno está inscrito en la clase
        if (!$student->subjects->contains($subject->id)) {
            return response()->json([
                'success' => false,
                'message' => 'El alumno no está inscrito en esta clase.'
            ], 422);
        }

        $note = StudentNote::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'note' => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nota agregada exitosamente.',
            'note' => $note->load(['teacher', 'subject.subjectType'])
        ]);
    }

    /**
     * Actualizar una nota existente
     */
    public function update(Request $request, StudentNote $note)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:teachers,id',
            'note_text' => 'required|string|max:2000',
        ], [
            'teacher_id.required' => 'El profesor es obligatorio.',
            'note_text.required' => 'La nota es obligatoria.',
            'note_text.max' => 'La nota no puede superar 2000 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $teacher = Teacher::findOrFail($request->teacher_id);

        // Verificar que el profesor puede editar esta nota
        if (!$note->canBeEditedBy($teacher)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar esta nota.'
            ], 403);
        }

        $note->update([
            'note' => $request->note_text,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nota actualizada exitosamente.',
            'note' => $note->fresh()->load(['teacher', 'subject.subjectType'])
        ]);
    }

    /**
     * Eliminar una nota
     */
    public function destroy(Request $request, StudentNote $note)
    {
        $teacherId = $request->input('teacher_id');
        
        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'ID de profesor no proporcionado.'
            ], 422);
        }

        $teacher = Teacher::findOrFail($teacherId);

        // Verificar que el profesor puede eliminar esta nota
        if (!$note->canBeEditedBy($teacher)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta nota.'
            ], 403);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nota eliminada exitosamente.'
        ]);
    }
}
