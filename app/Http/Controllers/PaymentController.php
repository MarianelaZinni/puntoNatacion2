<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            if (method_exists($student, 'calculateDebtFromCreationUsingCurrentMonthly')) {
                $calc = $student->calculateDebtFromCreationUsingCurrentMonthly();
                $student->debt = $calc['debt'];
                $student->monthly_amount = $calc['monthly_amount'];
                $student->unpaid_periods = $calc['unpaid_periods'];
                $student->next_unpaid_period = $calc['next_unpaid_period'];
                $student->has_debt = ($calc['debt'] > 0);
                $student->paid_this_month = $student->isPeriodFullyPaid(Carbon::now()->format('Y-m'), $student->monthly_amount);
            } else {
                $student->debt = 0;
                $student->monthly_amount = 0;
                $student->unpaid_periods = [];
                $student->next_unpaid_period = null;
                $student->has_debt = false;
                $student->paid_this_month = false;
            }
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
     * Historial de pagos (filtrable por student_id y por rango de periodos).
     *
     * El filtrado por periodo usa payment_period si existe; si payment_period es NULL
     * usa el primer día del mes de payment_date para comparar.
     *
     * Recibe:
     * - student_id (opcional)
     * - period_from (opcional) formato YYYY-MM
     * - period_to   (opcional) formato YYYY-MM
     */
    public function history(Request $request)
    {
        $studentId = $request->input('student_id');
        $periodFrom = $request->input('period_from'); // expected YYYY-MM
        $periodTo = $request->input('period_to');     // expected YYYY-MM
        $searchName = $request->input('search'); // no longer used in UI but kept for compatibility

        // Lista de alumnos para el select
        $students = Student::orderBy('name')->get();

        // Base query
        $paymentsQuery = Payment::with(['student', 'paymentMethod'])->latest('payment_date');

        // Filtrar por student_id si está presente
        if ($studentId) {
            $paymentsQuery->where('student_id', $studentId);
        }

        // Si el usuario envió periodos, normalizarlos a rango de fechas (primer día - último día)
        $fromDate = null;
        $toDate = null;
        try {
            if ($periodFrom && preg_match('/^\d{4}-\d{2}$/', $periodFrom)) {
                $fromDate = Carbon::createFromFormat('Y-m', $periodFrom)->startOfMonth()->toDateString(); // YYYY-MM-01
            }
            if ($periodTo && preg_match('/^\d{4}-\d{2}$/', $periodTo)) {
                $toDate = Carbon::createFromFormat('Y-m', $periodTo)->endOfMonth()->toDateString(); // YYYY-MM-31
            }
        } catch (\Throwable $e) {
            // ignore parse errors; we'll not apply period filter
            $fromDate = null;
            $toDate = null;
        }

        if ($fromDate || $toDate) {
            // Build a safe SQL expression that normalizes each payment to a "period date"
            // using COALESCE(payment_period, DATE_FORMAT(payment_date, '%Y-%m-01')).
            // Esto requiere MySQL DATE_FORMAT; asumimos MySQL según el proyecto.
            $periodExpr = "COALESCE(payment_period, DATE_FORMAT(payment_date, '%Y-%m-01'))";

            if ($fromDate && $toDate) {
                $paymentsQuery->whereRaw("$periodExpr BETWEEN ? AND ?", [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $paymentsQuery->whereRaw("$periodExpr >= ?", [$fromDate]);
            } elseif ($toDate) {
                $paymentsQuery->whereRaw("$periodExpr <= ?", [$toDate]);
            }
        }

        // Opción adicional: mantener búsqueda por nombre (si el usuario quiere)
        if ($searchName) {
            $paymentsQuery->whereHas('student', function ($q) use ($searchName) {
                $q->where('name', 'like', '%' . $searchName . '%');
            });
        }

        // Paginamos (ajusta el número por página si querés)
        $payments = $paymentsQuery->paginate(25)->withQueryString();

        // Si se pidió un alumno concreto, cargamos su resumen de deuda para mostrar el banner
        $selectedStudent = null;
        $debtSummary = null;
        if ($studentId) {
            $selectedStudent = Student::with(['subjects.subjectType', 'payments'])->find($studentId);
            if ($selectedStudent) {
                if (method_exists($selectedStudent, 'calculateDebtFromCreationUsingCurrentMonthly')) {
                    $debtSummary = $selectedStudent->calculateDebtFromCreationUsingCurrentMonthly();
                } else {
                    $totalPaid = $selectedStudent->payments()->sum('amount');
                    $debtSummary = [
                        'debt' => 0.0,
                        'monthly_amount' => 0.0,
                        'unpaid_periods' => [],
                        'selectable_periods' => [],
                        'next_unpaid_period' => null,
                        'total_paid' => (float)$totalPaid,
                    ];
                }
            }
        }

        return view('payments.history', compact('payments', 'students', 'studentId', 'searchName', 'selectedStudent', 'debtSummary'));
    }
}