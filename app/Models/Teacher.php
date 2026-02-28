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
     * Relación: Un profesor puede dictar varias clases
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
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
}
