<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_alumno_user_with_dni_only(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $student = Student::create([
            'dni' => '30123456',
            'name' => 'Alumno Vinculado',
            'email' => 'alumno.vinculado@example.com',
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'role' => User::ROLE_ALUMNO,
            'student_ids' => [$student->id],
            'name' => 'Usuario Alumno',
            'email' => '',
            'dni' => '32111222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasNoErrors();

        $user = User::where('dni', '32111222')->first();

        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_ALUMNO, $user->role);
        $this->assertNull($user->email);
        $this->assertTrue($user->students->contains($student));
    }

    public function test_admin_must_complete_email_or_dni_for_alumno_users(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $student = Student::create([
            'dni' => '30999888',
            'name' => 'Otro Alumno',
            'email' => 'otro.alumno@example.com',
        ]);

        $response = $this->actingAs($admin)->from(route('users.create'))->post(route('users.store'), [
            'role' => User::ROLE_ALUMNO,
            'student_ids' => [$student->id],
            'name' => 'Usuario Sin Identificador',
            'email' => '',
            'dni' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('users.create'));
        $response->assertSessionHasErrors(['email', 'dni']);
    }
}
