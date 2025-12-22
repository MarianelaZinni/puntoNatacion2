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
            $totalDue = isset($summary['total']) && $summary['total'] !== null ? (float) $summary['total'] : 0.0;

            // Detectar si existe al menos un pago en el mes en curso (en la colección cargada)
            $paidThisMonth = $student->payments->first(function ($p) use ($nowYm) {
                // payment_date puede ser string o Carbon, normalizamos
                $d = $p->payment_date;
                if ($d instanceof \Carbon\Carbon) {
                    $ym = $d->format('Y-m');
                } else {
                    $ym = Carbon::parse($d)->format('Y-m');
                }
                return $ym === $nowYm;
            }) !== null;

            // Si ya pagó este mes, mostramos deuda 0 y marcamos la bandera
            if ($paidThisMonth) {
                $student->debt = 0.0;
                $student->paid_this_month = true;
            } else {
                $student->debt = $totalDue;
                $student->paid_this_month = false;
            }

            // Exponemos el summary completo por si la vista necesita detalle
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
            'notes' => 'nullable|string|max:1000',
        ]);

        // Opción: prevenir pagos si ya existe pago en mes en curso (doble protección backend)
        $student = Student::findOrFail($data['student_id']);
        $paymentMonth = Carbon::parse($data['payment_date'])->format('Y-m');
        $alreadyPaid = $student->payments->first(function ($p) use ($paymentMonth) {
            $d = $p->payment_date;
            if ($d instanceof \Carbon\Carbon) {
                $ym = $d->format('Y-m');
            } else {
                $ym = Carbon::parse($d)->format('Y-m');
            }
            return $ym === $paymentMonth;
        });

        if ($alreadyPaid) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El alumno ya registra un pago en el mes seleccionado; no se permite registrar otro pago desde aquí.');
        }

        Payment::create([
            'student_id' => $data['student_id'],
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method_id' => $data['payment_method_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('payments.index', ['student_id' => $data['student_id']])
            ->with('success', 'Pago registrado correctamente.');
    }

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