<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentPause extends Model
{
    protected $fillable = [
        'student_id',
        'pause_period',
    ];

    protected $casts = [
        'pause_period' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * A pause period is considered "past" when its month is before the current month.
     * Past pauses cannot be edited or deleted.
     */
    public function isPast(): bool
    {
        return Carbon::parse($this->pause_period)->startOfMonth()->lt(Carbon::now()->startOfMonth());
    }

    /**
     * Returns the period formatted as MM/YYYY for display.
     */
    public function getPeriodFormattedAttribute(): string
    {
        try {
            return Carbon::parse($this->pause_period)->format('m/Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }

    /**
     * Returns the period as YYYY-MM (for use in month inputs).
     */
    public function getPeriodYmAttribute(): string
    {
        try {
            return Carbon::parse($this->pause_period)->format('Y-m');
        } catch (\Throwable $e) {
            return '';
        }
    }
}
