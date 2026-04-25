<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    const ROLE_ADMIN      = 'admin';
    const ROLE_ENFERMERIA = 'enfermeria';
    const ROLE_ALUMNO     = 'alumno';
    const ROLE_PROFESOR   = 'profesor';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
         'role',
        'teacher_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

     /***********************
     * Role helpers
     ***********************/

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEnfermeria(): bool
    {
        return $this->role === self::ROLE_ENFERMERIA;
    }

    public function isAlumno(): bool
    {
        return $this->role === self::ROLE_ALUMNO;
    }

    public function isProfesor(): bool
    {
        return $this->role === self::ROLE_PROFESOR;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN      => 'Administrador',
            self::ROLE_ENFERMERIA => 'Enfermería',
            self::ROLE_ALUMNO     => 'Alumno',
            self::ROLE_PROFESOR   => 'Profesor',
        ];
    }

    /***********************
     * Relationships
     ***********************/

    /**
     * Students linked to this user (for 'alumno' role, can be multiple).
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'user_students');
    }

    /**
     * Teacher linked to this user (for 'profesor' role).
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Announcements this user has marked as read.
     */
    public function readAnnouncements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_reads')
                    ->withPivot('read_at');
    }
}
