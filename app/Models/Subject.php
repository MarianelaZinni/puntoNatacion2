<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_type_id',
        'teacher_id',
        'substitute_teacher_id',
        'day',
        'start_time',
        'end_time',
        'capacity',
    ];

    /**
     * Relación con el tipo de materia (subject type).
     * Ajustá si tu proyecto usa otro nombre de modelo.
     */
    public function subjectType()
    {
        return $this->belongsTo(\App\Models\SubjectType::class, 'subject_type_id');
    }

    /**
     * Relación con el profesor titular de la clase
     */
    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    /**
     * Relación con el profesor suplente de la clase
     */
    public function substituteTeacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'substitute_teacher_id');
    }

    /**
     * Relación many-to-many con Student.
     * Especificamos el nombre correcto de la tabla pivot: 'student_subject'.
     */
    public function students()
    {
        return $this->belongsToMany(\App\Models\Student::class, 'student_subject', 'subject_id', 'student_id')
                    ->withTimestamps();
    }
}