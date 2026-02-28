<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Teacher extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'address',
        'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Relación: Un profesor puede dictar varias clases como titular
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }

    /**
     * Relación: Un profesor puede ser suplente en varias clases
     */
    public function subjectsAsSubstitute(): HasMany
    {
        return $this->hasMany(Subject::class, 'substitute_teacher_id');
    }

    /**
     * Accessor para obtener el nombre completo
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->surname}");
    }

    /**
     * Accessor para obtener la edad
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return Carbon::parse($this->birth_date)->age;
    }

    /**
     * Relación: Comentarios creados por este profesor
     */
    public function subjectComments(): HasMany
    {
        return $this->hasMany(SubjectComment::class);
    }

    /**
     * Check if teacher can comment on a subject
     * (must be titular or substitute teacher)
     */
    public function canCommentOnSubject(Subject $subject): bool
    {
        return $this->id === $subject->teacher_id || $this->id === $subject->substitute_teacher_id;
    }
}
