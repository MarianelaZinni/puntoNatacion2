<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubjectType extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'value'];

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
