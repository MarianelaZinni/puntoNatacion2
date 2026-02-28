<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the subject this comment belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher who created this comment.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Check if the comment can be edited by the given teacher.
     * Teacher must be the author AND still be assigned to the class (titular or substitute).
     */
    public function canBeEditedBy(Teacher $teacher): bool
    {
        // Must be the comment author
        if ($this->teacher_id !== $teacher->id) {
            return false;
        }

        // Must still be assigned to the class (titular or substitute)
        return $teacher->canCommentOnSubject($this->subject);
    }
}
