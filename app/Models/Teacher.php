<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dni',
        'email',
        'phone',
        'address',
        'observations',
    ];

    /**
     * Clases donde el profesor es titular.
     */
    public function titularSubjects()
    {
        return $this->hasMany(\App\Models\Subject::class, 'titular_teacher_id');
    }

    /**
     * Clases donde el profesor es suplente.
     */
    public function suplenteSubjects()
    {
        return $this->hasMany(\App\Models\Subject::class, 'suplente_teacher_id');
    }
}