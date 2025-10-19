<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['subject_type_id', 'capacity', 'day', 'start_time', 'end_time'];

    public function students()
    {
        return $this->belongsToMany(Student::class)->withTimestamps();
    }

    public function subjectType()
    {
        return $this->belongsTo(SubjectType::class);
    }
}
