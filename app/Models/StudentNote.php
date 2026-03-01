<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentNote extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject_id',
        'note',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el alumno
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relación con el profesor que escribió la nota
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Relación con la clase (subject)
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Verifica si un profesor puede editar esta nota
     * El profesor debe ser el autor de la nota Y aún estar asignado a la clase
     */
    public function canBeEditedBy(Teacher $teacher): bool
    {
        if ($this->teacher_id !== $teacher->id) {
            return false;
        }

        // Verificar que el profesor aún esté asignado a la clase
        return $teacher->canCommentOnSubject($this->subject);
    }

    /**
     * Log manual de creación de nota (por si se necesita en el futuro)
     */
    public static function logNote(Student $student, Teacher $teacher, Subject $subject, string $note): self
    {
        return self::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'note' => $note,
        ]);
    }
}
