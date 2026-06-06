<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClassNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'student_id',
        'teacher_user_id',
        'title',
        'body',
    ];

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

