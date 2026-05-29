<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_student_also_creates_alumno_user_with_dni_password(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->post(route('students.store'), [
            'dni' => '30123123',
            'name' => 'Alumno Auto Usuario',
            'email' => 'auto.usuario@example.com',
            'active_from' => now()->format('Y-m'),
        ]);

        $student = \App\Models\Student::where('dni', '30123123')->first();

        $response->assertRedirect(route('students.enrollClassForm', ['student' => $student->id]));
        $this->assertNotNull($student);

        $user = User::where('dni', '30123123')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_ALUMNO, $user->role);
        $this->assertTrue(Hash::check('30123123', $user->password));
        $this->assertTrue($user->students->contains($student));
    }
}
