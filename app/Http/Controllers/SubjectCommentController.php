<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectComment;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectCommentController extends Controller
{
    /**
     * Store a new comment for a subject.
     */
    public function store(Request $request, Subject $subject)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);

        // Verify teacher can comment on this subject (must be titular or substitute)
        if (!$teacher->canCommentOnSubject($subject)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para comentar en esta clase.',
            ], 403);
        }

        $comment = SubjectComment::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'comment' => $request->comment,
        ]);

        $comment->load('teacher');

        return response()->json([
            'success' => true,
            'message' => 'Comentario agregado exitosamente.',
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'teacher_name' => $comment->teacher->full_name,
                'created_at' => $comment->created_at->diffForHumans(),
                'created_at_full' => $comment->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Update an existing comment.
     */
    public function update(Request $request, SubjectComment $comment)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);

        // Verify teacher can edit this comment
        if (!$comment->canBeEditedBy($teacher)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este comentario.',
            ], 403);
        }

        $comment->update([
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentario actualizado exitosamente.',
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'updated_at' => $comment->updated_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, SubjectComment $comment)
    {
        $teacherId = $request->input('teacher_id');
        $teacher = Teacher::findOrFail($teacherId);

        // Verify teacher can delete this comment
        if (!$comment->canBeEditedBy($teacher)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este comentario.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comentario eliminado exitosamente.',
        ]);
    }
}
