<?php

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teachers') || ! Schema::hasTable('users')) {
            return;
        }

        Teacher::query()
            ->select(['id', 'name', 'dni', 'email'])
            ->orderBy('id')
            ->chunkById(100, function ($teachers) {
                foreach ($teachers as $teacher) {
                    // Prefer the user already linked by teacher_id FK
                    $user = User::query()->where('teacher_id', $teacher->id)->first();

                    // Fallback: match by DNI when an existing user shares the same DNI
                    if (! $user && filled($teacher->dni)) {
                        $user = User::query()->where('dni', $teacher->dni)->first();
                    }

                    if (! $user) {
                        $initialPassword = filled($teacher->dni) ? $teacher->dni : $teacher->name;

                        User::query()->create([
                            'name'       => $teacher->name,
                            'email'      => $teacher->email ?: null,
                            'dni'        => $teacher->dni ?: null,
                            'password'   => Hash::make($initialPassword),
                            'role'       => User::ROLE_PROFESOR,
                            'teacher_id' => $teacher->id,
                        ]);
                    } else {
                        $updates = [];

                        if ($user->role !== User::ROLE_PROFESOR) {
                            $updates['role'] = User::ROLE_PROFESOR;
                        }

                        if ((int) $user->teacher_id !== (int) $teacher->id) {
                            $updates['teacher_id'] = $teacher->id;
                        }

                        if (! empty($updates)) {
                            $user->update($updates);
                        }
                    }
                }
            });
    }

    public function down(): void
    {
        // Data migration intentionally non-reversible.
    }
};