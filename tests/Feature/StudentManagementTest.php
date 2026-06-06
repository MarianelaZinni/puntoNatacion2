<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectType;
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

    public function test_updating_an_enrolled_student_without_active_from_does_not_fail(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $student = Student::create([
            'dni' => '30123999',
            'name' => 'Alumno Sin Activacion',
            'email' => 'sin.activacion@example.com',
            'active_from' => null,
        ]);

        $subjectType = SubjectType::create([
            'description' => 'Natación',
            'value' => 10000,
        ]);

        $subject = Subject::create([
            'subject_type_id' => $subjectType->id,
            'capacity' => 10,
            'day' => 'Lunes',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
        ]);

        $student->subjects()->attach($subject->id);

        $response = $this->actingAs($admin)->put(route('students.update', ['student' => $student->id]), [
            'dni' => $student->dni,
            'name' => 'Alumno Editado',
            'email' => $student->email,
            'address' => null,
            'phone' => null,
            'observations' => null,
            'birth_date' => null,
            'active_from' => '',
        ]);

        $response->assertRedirect(route('students.index'));
        $response->assertSessionHasNoErrors('active_from');
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Alumno Editado',
        ]);
    }
}
