<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\PaymentMethod;
use App\Services\PriceCalculator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Muestra la página de registro de pagos.
     *
     * Opcionalmente acepta ?student_id= para preseleccionar un alumno en el formulario.
     */
    public function index(Request $request)
    {
        // Cargamos alumnos con sus subjects y payments (evita N+1)
        $students = Student::with(['subjects.subjectType', 'payments'])->orderBy('name')->get();

        // Calculamos deuda (resumen de precio) para cada alumno usando el helper del modelo
        foreach ($students as $student) {
            $calc = $student->calculateDebtFromCreationUsingCurrentMonthly();
            $student->debt = $calc['debt'];
            $student->monthly_amount = $calc['monthly_amount'];
            $student->unpaid_periods = $calc['unpaid_periods'];
            $student->selectable_periods = $calc['selectable_periods'];
            $student->next_unpaid_period = $calc['next_unpaid_period'];
            $student->has_debt = ($calc['debt'] > 0);

            // marca si pagó este mes completamente (usa la lógica que considera monthly_amount > 0)
            $nowYm = Carbon::now()->format('Y-m');
            $student->paid_this_month = $student->isPeriodFullyPaid($nowYm, $student->monthly_amount);
        }

        // Métodos de pago para el select
        $paymentMethods = PaymentMethod::orderBy('name')->get();

        // student_id pasado por query (al venir desde students.index)
        $selectedStudentId = $request->query('student_id');

        return view('payments.index', compact('students', 'paymentMethods', 'selectedStudentId'));
    }

    /**
     * Guarda un nuevo pago.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            // recibimos el periodo como 'YYYY-MM' vía select
            'payment_period' => ['required','regex:/^\d{4}-\d{2}$/'],
            'notes' => 'nullable|string|max:1000',
        ]);

        // Normalizar payment_period: si vino como YYYY-MM, lo convertimos a YYYY-MM-01 para almacenar
        try {
            $periodCarbon = Carbon::createFromFormat('Y-m', $data['payment_period'])->startOfMonth();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Período inválido.');
        }

        // Guardar el pago (permitimos pagos parciales por periodo)
        Payment::create([
            'student_id' => $data['student_id'],
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_period' => $periodCarbon->toDateString(),
            'payment_method_id' => $data['payment_method_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('payments.index', ['student_id' => $data['student_id']])
            ->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Historial de pagos (filtrable por student_id).
     * Devuelve $payments (paginados), $students (para el select) y, si se seleccionó un alumno,
     * su resumen de deuda calculado con Student::calculateDebtFromCreationUsingCurrentMonthly().
     */
    public function history(Request $request)
    {
        $studentId = $request->input('student_id');
        $searchName = $request->input('search'); // opcional

        // Lista de alumnos para el select
        $students = Student::orderBy('name')->get();

        // Query de pagos
        $paymentsQuery = Payment::with(['student', 'paymentMethod'])->latest('payment_date');

        // Filtrar por student_id si está presente
        if ($studentId) {
            $paymentsQuery->where('student_id', $studentId);
        }

        // Filtro adicional por nombre (si se usa)
        if ($searchName) {
            $paymentsQuery->whereHas('student', function ($q) use ($searchName) {
                $q->where('name', 'like', '%' . $searchName . '%');
            });
        }

        // Paginamos
        $payments = $paymentsQuery->paginate(25)->withQueryString();

        // Si se pidió un alumno concreto, cargamos su summary de deuda para mostrar el banner
        $selectedStudent = null;
        $debtSummary = null;
        if ($studentId) {
            $selectedStudent = Student::with(['subjects.subjectType', 'payments'])->find($studentId);
            if ($selectedStudent) {
                // Usa el helper del modelo que calcula deuda desde la creación usando la cuota actual
                if (method_exists($selectedStudent, 'calculateDebtFromCreationUsingCurrentMonthly')) {
                    $debtSummary = $selectedStudent->calculateDebtFromCreationUsingCurrentMonthly();
                } else {
                    // Fallback simple: sumar pagos y setear debt 0 (no queremos romper la vista)
                    $totalPaid = $selectedStudent->payments()->sum('amount');
                    $debtSummary = [
                        'debt' => 0.0,
                        'monthly_amount' => 0.0,
                        'unpaid_periods' => [],
                        'selectable_periods' => [],
                        'next_unpaid_period' => null,
                    ];
                    $debtSummary['total_paid'] = (float)$totalPaid;
                }
            }
        }

        return view('payments.history', compact('payments', 'students', 'studentId', 'searchName', 'selectedStudent', 'debtSummary'));
    }
}