<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\Payment;
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

    public function test_updating_an_enrolled_student_without_active_from_can_set_it(): void
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

        // Admin sets active_from for the first time even though student is enrolled
        $response = $this->actingAs($admin)->put(route('students.update', ['student' => $student->id]), [
            'dni' => $student->dni,
            'name' => 'Alumno Editado',
            'email' => $student->email,
            'address' => null,
            'phone' => null,
            'observations' => null,
            'birth_date' => null,
            'active_from' => '2023-02',
        ]);

        $response->assertRedirect(route('students.index'));
        $response->assertSessionHasNoErrors();
        $student->refresh();

        $this->assertSame('Alumno Editado', $student->name);
        $this->assertSame('2023-02-01', optional($student->active_from)->toDateString());
    }

    public function test_updating_an_enrolled_student_without_active_from_can_keep_it_empty(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $student = Student::create([
            'dni' => '30123998',
            'name' => 'Alumno Sin Activacion',
            'email' => 'sin.activacion.vacio@example.com',
            'active_from' => null,
        ]);

        $subjectType = SubjectType::create([
            'description' => 'Natación',
            'value' => 10000,
        ]);

        $subject = Subject::create([
            'subject_type_id' => $subjectType->id,
            'capacity' => 10,
            'day' => 'Jueves',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
        ]);

        $student->subjects()->attach($subject->id);

        $response = $this->actingAs($admin)->put(route('students.update', ['student' => $student->id]), [
            'dni' => $student->dni,
            'name' => 'Alumno Editado Sin Periodo',
            'email' => $student->email,
            'address' => null,
            'phone' => null,
            'observations' => null,
            'birth_date' => null,
            'active_from' => '',
        ]);

        $response->assertRedirect(route('students.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Alumno Editado Sin Periodo',
            'active_from' => null,
        ]);
    }

    public function test_updating_an_enrolled_student_with_active_from_set_cannot_change_it(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $student = Student::create([
            'dni' => '30124000',
            'name' => 'Alumno Con Activacion',
            'email' => 'con.activacion@example.com',
            'active_from' => '2023-01-01',
        ]);

        $subjectType = SubjectType::create([
            'description' => 'Natación',
            'value' => 10000,
        ]);

        $subject = Subject::create([
            'subject_type_id' => $subjectType->id,
            'capacity' => 10,
            'day' => 'Martes',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        $student->subjects()->attach($subject->id);

        // Admin tries to change active_from — it should be silently preserved
        $response = $this->actingAs($admin)->put(route('students.update', ['student' => $student->id]), [
            'dni' => $student->dni,
            'name' => 'Alumno Editado',
            'email' => $student->email,
            'active_from' => '2023-06',
        ]);

        $response->assertRedirect(route('students.index'));
        $response->assertSessionHasNoErrors();
        // active_from must remain unchanged
        $student->refresh();

        $this->assertSame('2023-01-01', optional($student->active_from)->toDateString());
    }

    public function test_updating_student_with_payment_history_and_no_classes_can_keep_active_from_empty(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $student = Student::create([
            'dni' => '30124001',
            'name' => 'Alumno Con Historial',
            'email' => 'historial@example.com',
            'active_from' => null,
        ]);

        Payment::create([
            'student_id' => $student->id,
            'payment_method_id' => null,
            'amount' => 5000,
            'expected_amount' => 5000,
            'payment_date' => now()->toDateString(),
            'payment_period' => now()->startOfMonth()->toDateString(),
            'payment_type' => Payment::TYPE_NORMAL,
            'notes' => null,
        ]);

        $response = $this->actingAs($admin)->put(route('students.update', ['student' => $student->id]), [
            'dni' => $student->dni,
            'name' => 'Alumno Con Historial Editado',
            'email' => $student->email,
            'address' => null,
            'phone' => null,
            'observations' => null,
            'birth_date' => null,
            'active_from' => '',
        ]);

        $response->assertRedirect(route('students.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Alumno Con Historial Editado',
            'active_from' => null,
        ]);
    }
}
