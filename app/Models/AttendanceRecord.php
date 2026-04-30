<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_list_id',
        'student_id',
        'present',
        'observations',
    ];

    protected $casts = [
        'present' => 'boolean',
    ];

    public function attendanceList()
    {
        return $this->belongsTo(AttendanceList::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Proxy: return the date from the parent attendance list.
     * Keeps the students/partials/recent_attendance view working without changes.
     */
    public function getDateAttribute(): ?\Carbon\Carbon
    {
        return $this->attendanceList?->date;
    }

    /**
     * Proxy: return the subject from the parent attendance list.
     * Keeps the students/partials/recent_attendance view working without changes.
     */
    public function getSubjectAttribute(): ?Subject
    {
        return $this->attendanceList?->subject;
    }
}