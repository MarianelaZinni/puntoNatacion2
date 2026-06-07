<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassMonthlyPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'month',
        'content',
        'created_by',
    ];

    protected $casts = [
        'month' => 'date',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
