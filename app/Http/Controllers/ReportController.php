<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Student;
use App\Models\Payment;
use App\Services\PriceCalculator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // barryvdh/laravel-dompdf facade

class ReportController extends Controller
{
    protected $companyName = 'Punto Natación'; // ajustá si querés

    public function index()
    {
        return view('reports.index');
    }

    //
    // 1) Inscritos por clase
    //
    public function classEnrolleesForm()
    {
        // cargamos las clases con su tipo y horario (ajustá relaciones si tenés otros nombres)
        $classes = Subject::with('subjectType')->withCount('students')->orderBy('day')->orderBy('start_time')->get();
        return view('reports.class_enrollees_form', compact('classes'));
    }

    /**
     * Mostrar vista para imprimir / preview de inscritos por clase.
     *
     * Recibe query param subject_id (opcional).
     */
    public function classEnrollees(Request $request)
    {
        $subjectId = $request->query('subject_id');

        // también cargamos $classes para evitar errores si la vista parcial lo necesita
        $classes = Subject::with('subjectType')->withCount('students')->orderBy('day')->orderBy('start_time')->get();

        $subject = null;
        $students = collect();

        if ($subjectId) {
            $subject = Subject::with(['students' => function ($q) {
                $q->orderBy('name');
            }, 'subjectType'])->find($subjectId);

            if ($subject) {
                $students = $subject->students()->orderBy('name')->get();
            }
        }

        // Vista de impresión simple
        return view('reports.class_enrollees', [
            'company' => $this->companyName,
            'subject' => $subject,
            'students' => $students,
            'classes' => $classes,
            'generated_at' => Carbon::now(),
        ]);
    }

    /**
     * Genera y descarga PDF del reporte de inscriptos por clase.
     */
    public function classEnrolleesPdf(Request $request)
    {
        $subjectId = $request->query('subject_id');

        $classes = Subject::with('subjectType')->withCount('students')->orderBy('day')->orderBy('start_time')->get();

        $subject = null;
        $students = collect();

        if ($subjectId) {
            $subject = Subject::with(['students' => function ($q) {
                $q->orderBy('name');
            }, 'subjectType'])->find($subjectId);

            if ($subject) {
                $students = $subject->students()->orderBy('name')->get();
            }
        }

        $html = view('reports.class_enrollees', [
            'company' => $this->companyName,
            'subject' => $subject,
            'students' => $students,
            'classes' => $classes,
            'generated_at' => Carbon::now(),
        ])->render();

        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        $filename = 'inscriptos_clase_' . ($subjectId ?: 'all') . '.pdf';
        return $pdf->download($filename);
    }

    //
    // 2) Alumnos deudores
    //
    public function debtorsForm()
    {
        return view('reports.debtors_form');
    }

    public function debtors(Request $request)
    {
        // opciones de filtrado: min_debt (float), limit (int)
        $minDebt = is_numeric($request->query('min_debt')) ? (float)$request->query('min_debt') : 1.0;
        $limit = is_numeric($request->query('limit')) ? (int)$request->query('limit') : 1000;

        // Cargar estudiantes, eager loads
        $students = Student::with(['subjects', 'payments'])->get();

        // Calculamos deuda con la función de modelo que ya tenés (calculateDebtFromCreationUsingCurrentMonthly)
        $debtors = $students->map(function ($s) {
            if (method_exists($s, 'calculateDebtFromCreationUsingCurrentMonthly')) {
                $calc = $s->calculateDebtFromCreationUsingCurrentMonthly();
                $s->debt = $calc['debt'];
                $s->unpaid_periods = $calc['unpaid_periods'];
            } else {
                $s->debt = 0.0;
                $s->unpaid_periods = [];
            }
            return $s;
        })->filter(function ($s) use ($minDebt) {
            return ((float)$s->debt >= $minDebt);
        })->sortByDesc('debt')->take($limit);

        return view('reports.debtors', [
            'company' => $this->companyName,
            'debtors' => $debtors,
            'min_debt' => $minDebt,
            'generated_at' => Carbon::now(),
        ]);
    }

    public function debtorsPdf(Request $request)
    {
        $viewData = $this->debtors($request)->render(); // devuelve HTML
        $pdf = PDF::loadHTML($viewData)->setPaper('a4', 'portrait');
        return $pdf->download('alumnos_deudores.pdf');
    }

    //
    // 3) Pagos por alumno
    //
    public function paymentsByStudentForm()
    {
        $students = Student::orderBy('name')->get();
        return view('reports.payments_by_student_form', compact('students'));
    }

    public function paymentsByStudent(Request $request)
    {
        $studentId = $request->query('student_id');
        $periodFrom = $request->query('period_from'); // YYYY-MM
        $periodTo = $request->query('period_to');     // YYYY-MM

        $paymentsQuery = Payment::with(['paymentMethod', 'student'])->latest('payment_date');

        if ($studentId) {
            $paymentsQuery->where('student_id', $studentId);
        }

        // Filtrar por payment_period si existe, fallback a payment_date month
        if ($periodFrom || $periodTo) {
            try {
                if ($periodFrom && preg_match('/^\d{4}-\d{2}$/', $periodFrom)) {
                    $from = Carbon::createFromFormat('Y-m', $periodFrom)->startOfMonth()->toDateString();
                } else {
                    $from = null;
                }
                if ($periodTo && preg_match('/^\d{4}-\d{2}$/', $periodTo)) {
                    $to = Carbon::createFromFormat('Y-m', $periodTo)->endOfMonth()->toDateString();
                } else {
                    $to = null;
                }
            } catch (\Throwable $e) {
                $from = $to = null;
            }

            if ($from || $to) {
                $periodExpr = "COALESCE(payment_period, DATE_FORMAT(payment_date, '%Y-%m-01'))";
                if ($from && $to) {
                    $paymentsQuery->whereRaw("$periodExpr BETWEEN ? AND ?", [$from, $to]);
                } elseif ($from) {
                    $paymentsQuery->whereRaw("$periodExpr >= ?", [$from]);
                } elseif ($to) {
                    $paymentsQuery->whereRaw("$periodExpr <= ?", [$to]);
                }
            }
        }

        $payments = $paymentsQuery->get();

        $student = $studentId ? Student::find($studentId) : null;

        return view('reports.payments_by_student', [
            'company' => $this->companyName,
            'student' => $student,
            'payments' => $payments,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'generated_at' => Carbon::now(),
        ]);
    }

    public function paymentsByStudentPdf(Request $request)
    {
        $html = $this->paymentsByStudent($request)->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        return $pdf->download('pagos_por_alumno.pdf');
    }

    //
    // 4) Todos los alumnos
    //
public function allStudents(Request $request)
{
    $students = Student::with(['subjects'])->orderBy('name')->get();

    return view('reports.all_students', [
        'company' => $this->companyName,
        'students' => $students,
        'generated_at' => Carbon::now(),
        // por si la vista (o alguna inclusión) espera $student o $payments
        'student' => null,
        'payments' => collect(),
    ]);
}

public function allStudentsPdf(Request $request)
{
    $students = Student::with(['subjects'])->orderBy('name')->get();

    $html = view('reports.all_students', [
        'company' => $this->companyName,
        'students' => $students,
        'generated_at' => Carbon::now(),
        'student' => null,
        'payments' => collect(),
    ])->render();

    $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
    return $pdf->download('todos_los_alumnos.pdf');
}

public function allStudentsPage(Request $request)
{
    $students = \App\Models\Student::with(['subjects.subjectType'])->orderBy('name')->get();

    return view('reports.all_students_page', [
        'company' => $this->companyName ?? 'Mi Escuela',
        'students' => $students,
        'generated_at' => \Carbon\Carbon::now(),
    ]);
}

    /**
 * Mostrar el listado de inscriptos por clase, dentro del layout de la app.
 * Ruta: GET /reports/class-enrollees/view?subject_id=...
 */
public function classEnrolleesPage(Request $request)
{
    $subjectId = $request->query('subject_id');

    // cargar todas las clases para el select y/o context
    $classes = Subject::with('subjectType')->withCount('students')->orderBy('day')->orderBy('start_time')->get();

    $subject = null;
    $students = collect();

    if ($subjectId) {
        $subject = Subject::with(['students' => function ($q) {
            $q->orderBy('name');
        }, 'subjectType'])->find($subjectId);

        if ($subject) {
            $students = $subject->students()->orderBy('name')->get();
        }
    }

    return view('reports.class_enrollees_page', [
        'company' => $this->companyName,
        'classes' => $classes,
        'subject' => $subject,
        'students' => $students,
        'generated_at' => Carbon::now(),
    ]);
}

/**
 * Mostrar listado de alumnos deudores dentro del layout (página).
 * Ruta: GET /reports/debtors/view
 */
public function debtorsPage(Request $request)
{
    // opciones de filtrado: min_debt (float), limit (int)
    $minDebt = is_numeric($request->query('min_debt')) ? (float)$request->query('min_debt') : 1.0;
    $limit = is_numeric($request->query('limit')) ? (int)$request->query('limit') : 1000;

    // Cargar estudiantes, eager loads
    $students = Student::with(['subjects', 'payments'])->get();

    // Calculamos deuda con la función de modelo que ya tenés
    $debtors = $students->map(function ($s) {
        if (method_exists($s, 'calculateDebtFromCreationUsingCurrentMonthly')) {
            $calc = $s->calculateDebtFromCreationUsingCurrentMonthly();
            $s->debt = $calc['debt'];
            $s->unpaid_periods = $calc['unpaid_periods'];
        } else {
            $s->debt = 0.0;
            $s->unpaid_periods = [];
        }
        return $s;
    })->filter(function ($s) use ($minDebt) {
        return ((float)$s->debt >= $minDebt);
    })->sortByDesc('debt')->take($limit);

    return view('reports.debtors_page', [
        'company' => $this->companyName,
        'debtors' => $debtors,
        'min_debt' => $minDebt,
        'generated_at' => Carbon::now(),
    ]);
}

/**
 * Mostrar listado de pagos por alumno dentro del layout (página).
 * Ruta: GET /reports/payments-by-student/view
 */
public function paymentsByStudentPage(Request $request)
{
    $studentId = $request->query('student_id');
    $periodFrom = $request->query('period_from'); // YYYY-MM
    $periodTo = $request->query('period_to');     // YYYY-MM

    $paymentsQuery = \App\Models\Payment::with(['paymentMethod', 'student'])->latest('payment_date');

    if ($studentId) {
        $paymentsQuery->where('student_id', $studentId);
    }

    // Filtrar por payment_period si existe, fallback a payment_date month
    if ($periodFrom || $periodTo) {
        try {
            if ($periodFrom && preg_match('/^\d{4}-\d{2}$/', $periodFrom)) {
                $from = Carbon::createFromFormat('Y-m', $periodFrom)->startOfMonth()->toDateString();
            } else {
                $from = null;
            }
            if ($periodTo && preg_match('/^\d{4}-\d{2}$/', $periodTo)) {
                $to = Carbon::createFromFormat('Y-m', $periodTo)->endOfMonth()->toDateString();
            } else {
                $to = null;
            }
        } catch (\Throwable $e) {
            $from = $to = null;
        }

        if ($from || $to) {
            // Usa COALESCE para normalizar payment_period o primer día del payment_date
            $periodExpr = "COALESCE(payment_period, DATE_FORMAT(payment_date, '%Y-%m-01'))";
            if ($from && $to) {
                $paymentsQuery->whereRaw("$periodExpr BETWEEN ? AND ?", [$from, $to]);
            } elseif ($from) {
                $paymentsQuery->whereRaw("$periodExpr >= ?", [$from]);
            } elseif ($to) {
                $paymentsQuery->whereRaw("$periodExpr <= ?", [$to]);
            }
        }
    }

    $payments = $paymentsQuery->get();

    $student = $studentId ? \App\Models\Student::find($studentId) : null;

    return view('reports.payments_by_student_page', [
        'company' => $this->companyName ?? 'Mi Escuela',
        'student' => $student,
        'payments' => $payments,
        'period_from' => $periodFrom,
        'period_to' => $periodTo,
        'generated_at' => Carbon::now(),
    ]);
}
}