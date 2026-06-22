<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClassNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'student_id',   // null = nota general para toda la clase
        'teacher_user_id',
        'title',
        'body',
    ];

    /**
     * Returns true if this note targets all students in the class (no specific student).
     */
    public function isGroupNote(): bool
    {
        return $this->student_id === null;
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'student_class_note_reads')
            ->withPivot('read_at');
    }
}