<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPause extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'start_date', 'end_date', 'reason'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * A pause can only be edited or deleted while its end_date has not yet passed.
     */
    public function isEditable(): bool
    {
        return $this->end_date->gte(Carbon::today());
    }

    /**
     * Returns true if the pause is currently active (today falls within the range).
     */
    public function isActive(): bool
    {
        $today = Carbon::today();
        return $this->start_date->lte($today) && $this->end_date->gte($today);
    }

    /**
     * Returns true if this pause overlaps the given calendar month.
     *
     * @param Carbon $monthStart  The first day of the month (startOfMonth).
     */
    public function overlapsMonth(Carbon $monthStart): bool
    {
        $monthEnd = $monthStart->copy()->endOfMonth();
        return $this->start_date->lte($monthEnd) && $this->end_date->gte($monthStart);
    }
}