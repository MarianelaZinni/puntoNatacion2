<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalCheckup extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'checkup_date',
        'period',
        'approved',
        'observations',
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'period' => 'date',
        'approved' => 'boolean',
    ];

    /**
     * Relación con Student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}