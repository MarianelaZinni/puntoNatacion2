<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasTable('users') || ! Schema::hasTable('user_students')) {
            return;
        }

        Student::query()
            ->select(['id', 'name', 'dni'])
            ->orderBy('id')
            ->chunkById(100, function ($students) {
                foreach ($students as $student) {
                    $user = User::query()->where('dni', $student->dni)->first();

                    if (! $user) {
                        $user = User::query()->create([
                            'name' => $student->name,
                            'email' => null,
                            'dni' => $student->dni,
                            'password' => Hash::make($student->dni),
                            'role' => User::ROLE_ALUMNO,
                            'teacher_id' => null,
                        ]);
                    } else {
                        $updates = [];

                        if ($user->role !== User::ROLE_ALUMNO) {
                            $updates['role'] = User::ROLE_ALUMNO;
                        }

                        if ($user->teacher_id !== null) {
                            $updates['teacher_id'] = null;
                        }

                        if (! empty($updates)) {
                            $user->update($updates);
                        }
                    }

                    $user->students()->syncWithoutDetaching([$student->id]);
                }
            });
    }

    public function down(): void
    {
        // Data migration intentionally non-reversible.
    }
};