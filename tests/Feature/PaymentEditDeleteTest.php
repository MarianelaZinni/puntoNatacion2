<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentEditDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;
    private PaymentMethod $paymentMethod;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Student::create([
            'name' => 'Juan Pérez',
            'dni' => '12345678',
        ]);

        $this->paymentMethod = PaymentMethod::create([
            'name' => 'Efectivo',
            'slug' => 'efectivo',
        ]);

        $this->payment = Payment::create([
            'student_id' => $this->student->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 5000.00,
            'expected_amount' => 5000.00,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_period' => Carbon::now()->startOfMonth()->toDateString(),
            'notes' => 'Pago de prueba',
        ]);
    }

    /** @test */
    public function edit_payment_form_is_accessible(): void
    {
        $this->withoutVite();
        $response = $this->get(route('payments.edit', $this->payment));

        $response->assertStatus(200);
        $response->assertViewIs('payments.edit');
        $response->assertViewHas('payment');
        $response->assertViewHas('students');
        $response->assertViewHas('paymentMethods');
    }

    /** @test */
    public function edit_form_shows_current_payment_data(): void
    {
        $this->withoutVite();
        $response = $this->get(route('payments.edit', $this->payment));

        $response->assertStatus(200);
        $response->assertSee($this->student->name);
        $response->assertSee($this->paymentMethod->name);
        $response->assertSee('Pago de prueba');
    }

    /** @test */
    public function payment_can_be_updated(): void
    {
        $newStudent = Student::create([
            'name' => 'María García',
            'dni' => '87654321',
        ]);

        $response = $this->put(route('payments.update', $this->payment), [
            'student_id' => $newStudent->id,
            'amount' => 6000.00,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_period' => Carbon::now()->format('Y-m'),
            'payment_method_id' => $this->paymentMethod->id,
            'notes' => 'Pago actualizado',
        ]);

        $response->assertRedirect(route('payments.history', ['student_id' => $newStudent->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'student_id' => $newStudent->id,
            'notes' => 'Pago actualizado',
        ]);
    }

    /** @test */
    public function payment_amount_is_updated_correctly(): void
    {
        $this->put(route('payments.update', $this->payment), [
            'student_id' => $this->student->id,
            'amount' => 7500.50,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_period' => Carbon::now()->format('Y-m'),
            'payment_method_id' => $this->paymentMethod->id,
            'notes' => 'Monto actualizado',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'amount' => 7500.50,
        ]);
    }

    /** @test */
    public function payment_method_can_be_set_to_null(): void
    {
        $response = $this->put(route('payments.update', $this->payment), [
            'student_id' => $this->student->id,
            'amount' => 5000.00,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_period' => Carbon::now()->format('Y-m'),
            'payment_method_id' => null,
            'notes' => null,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'payment_method_id' => null,
        ]);
    }

    /** @test */
    public function update_payment_requires_valid_student(): void
    {
        $response = $this->put(route('payments.update', $this->payment), [
            'student_id' => 99999,
            'amount' => 5000.00,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_period' => Carbon::now()->format('Y-m'),
        ]);

        $response->assertSessionHasErrors('student_id');
    }

    /** @test */
    public function update_payment_requires_positive_amount(): void
    {
        $response = $this->put(route('payments.update', $this->payment), [
            'student_id' => $this->student->id,
            'amount' => 0,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_period' => Carbon::now()->format('Y-m'),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function update_payment_requires_valid_period_format(): void
    {
        $response = $this->put(route('payments.update', $this->payment), [
            'student_id' => $this->student->id,
            'amount' => 5000.00,
            'payment_date' => Carbon::now()->toDateString(),
            'payment_period' => 'periodo-invalido',
        ]);

        $response->assertSessionHasErrors('payment_period');
    }

    /** @test */
    public function payment_can_be_deleted(): void
    {
        $paymentId = $this->payment->id;
        $studentId = $this->student->id;

        $response = $this->delete(route('payments.destroy', $this->payment));

        $response->assertRedirect(route('payments.history', ['student_id' => $studentId]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('payments', ['id' => $paymentId]);
    }

    /** @test */
    public function deleting_payment_redirects_to_student_history(): void
    {
        $studentId = $this->student->id;

        $response = $this->delete(route('payments.destroy', $this->payment));

        $response->assertRedirect(route('payments.history', ['student_id' => $studentId]));
    }

    /** @test */
    public function history_page_shows_edit_and_delete_buttons(): void
    {
        $this->withoutVite();
        $response = $this->get(route('payments.history'));

        $response->assertStatus(200);
        $response->assertSee(route('payments.edit', $this->payment));
        $response->assertSee(route('payments.destroy', $this->payment));
    }

    /** @test */
    public function editing_nonexistent_payment_returns_404(): void
    {
        $response = $this->get(route('payments.edit', 99999));

        $response->assertStatus(404);
    }

    /** @test */
    public function deleting_nonexistent_payment_returns_404(): void
    {
        $response = $this->delete(route('payments.destroy', 99999));

        $response->assertStatus(404);
    }
}
