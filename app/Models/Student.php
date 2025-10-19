<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;
    
    protected $fillable = ['dni', 'name', 'address', 'email', 'phone'];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }
}
