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

        // Calculamos deuda (resumen de precio) para cada alumno usando PriceCalculator
        $calculator = new PriceCalculator();
        $nowYm = Carbon::now()->format('Y-m');

        foreach ($students as $student) {
            // PriceCalculator espera una colección de subjects
            $summary = $calculator->calculate($student->subjects);
            $monthlyTotal = isset($summary['total']) && $summary['total'] !== null ? (float) $summary['total'] : 0.0;

            // Suma de pagos para el periodo actual (payment_period)
            $paidThisPeriod = $student->payments->filter(function ($p) use ($nowYm) {
                if (!$p->payment_period) return false;
                $ym = Carbon::parse($p->payment_period)->format('Y-m');
                return $ym === $nowYm;
            })->sum('amount');

            $debt = max(0, $monthlyTotal - $paidThisPeriod);
            $student->debt = $debt;
            $student->paid_this_month = ($paidThisPeriod >= $monthlyTotal && $monthlyTotal > 0);
            $student->price_summary = $summary;
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
            // recibimos el periodo como 'YYYY-MM' vía input type="month"
            'payment_period' => ['nullable','regex:/^\d{4}-\d{2}$/'],
            'notes' => 'nullable|string|max:1000',
        ]);

        // Normalizar payment_period: si vino como YYYY-MM, lo convertimos a YYYY-MM-01 para almacenar
        if (!empty($data['payment_period'])) {
            try {
                $periodCarbon = Carbon::createFromFormat('Y-m', $data['payment_period'])->startOfMonth();
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()->with('error', 'Período inválido.');
            }
        } else {
            // si no viene payment_period, lo deducimos desde payment_date
            $periodCarbon = Carbon::parse($data['payment_date'])->startOfMonth();
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
     * Devuelve $payments (paginados) y $students (para el select).
     */
    public function history(Request $request)
    {
        $studentId = $request->input('student_id');
        $searchName = $request->input('search'); // opcional, si querés mantener búsqueda por nombre también

        // Lista de alumnos para el select
        $students = Student::orderBy('name')->get();

        // Query de pagos
        $paymentsQuery = Payment::with(['student', 'paymentMethod'])->latest('payment_date');

        // Filtrar por student_id si está presente
        if ($studentId) {
            $paymentsQuery->where('student_id', $studentId);
        }

        // Opción adicional: mantener búsqueda por nombre (si el usuario quiere)
        if ($searchName) {
            $paymentsQuery->whereHas('student', function ($q) use ($searchName) {
                $q->where('name', 'like', '%' . $searchName . '%');
            });
        }

        // Paginamos (ajusta el número por página si querés)
        $payments = $paymentsQuery->paginate(25)->withQueryString();

        return view('payments.history', compact('payments', 'students', 'studentId', 'searchName'));
    }
}