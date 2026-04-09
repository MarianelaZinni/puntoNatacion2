<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Student;
use App\Models\Payment;
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
    // Private helper methods
    //

    /**
     * Fetch data for class enrollees report
     */
    private function fetchClassEnrolleesData(Request $request): array
    {
        $subjectId = $request->query('subject_id');

        // Load all classes for forms and context
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

        return [
            'company' => $this->companyName,
            'subject' => $subject,
            'students' => $students,
            'classes' => $classes,
            'generated_at' => Carbon::now(),
        ];
    }

    /**
     * Fetch data for debtors report
     */
    private function fetchDebtorsData(Request $request): array
    {
        $minDebt = is_numeric($request->query('min_debt')) ? (float)$request->query('min_debt') : 1.0;
        $limit = is_numeric($request->query('limit')) ? (int)$request->query('limit') : 1000;

        // Load students with relations
        $students = Student::with(['subjects', 'payments'])->get();

        // Calculate debt
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

        return [
            'company' => $this->companyName,
            'debtors' => $debtors,
            'min_debt' => $minDebt,
            'generated_at' => Carbon::now(),
        ];
    }

    /**
     * Fetch data for payments by student report
     */
    private function fetchPaymentsByStudentData(Request $request): array
    {
        $studentId = $request->query('student_id');
        $periodFrom = $request->query('period_from'); // YYYY-MM
        $periodTo = $request->query('period_to');     // YYYY-MM

        $paymentsQuery = Payment::with(['paymentMethod', 'student'])->latest('payment_date');

        if ($studentId) {
            $paymentsQuery->where('student_id', $studentId);
        }

        // Filter by payment_period if exists, fallback to payment_date month
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

        return [
            'company' => $this->companyName,
            'student' => $student,
            'payments' => $payments,
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'generated_at' => Carbon::now(),
        ];
    }

    /**
     * Fetch data for all students report
     */
    private function fetchAllStudentsData(Request $request): array
    {
        $students = Student::with(['subjects.subjectType'])->orderBy('name')->get();

        return [
            'company' => $this->companyName,
            'students' => $students,
            'generated_at' => Carbon::now(),
        ];
    }

    /**
     * Render and download PDF from view
     */
    private function renderPdfFromView(string $view, array $data, string $filename)
    {
        $html = view($view, $data)->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'portrait');
        return $pdf->download($filename);
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
        $data = $this->fetchClassEnrolleesData($request);
        return view('reports.class_enrollees', $data);
    }

    /**
     * Genera y descarga PDF del reporte de inscriptos por clase.
     */
    public function classEnrolleesPdf(Request $request)
    {
        $data = $this->fetchClassEnrolleesData($request);
        $subjectId = $request->query('subject_id');
        $filename = 'inscriptos_clase_' . ($subjectId ?: 'all') . '.pdf';
        return $this->renderPdfFromView('reports.class_enrollees', $data, $filename);
    }

    /**
     * Mostrar el listado de inscriptos por clase, dentro del layout de la app.
     * Ruta: GET /reports/class-enrollees/view?subject_id=...
     */
    public function classEnrolleesPage(Request $request)
    {
        $data = $this->fetchClassEnrolleesData($request);
        return view('reports.class_enrollees_page', $data);
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
        $data = $this->fetchDebtorsData($request);
        return view('reports.debtors', $data);
    }

    public function debtorsPdf(Request $request)
    {
        $data = $this->fetchDebtorsData($request);
        return $this->renderPdfFromView('reports.debtors', $data, 'alumnos_deudores.pdf');
    }

    /**
     * Mostrar listado de alumnos deudores dentro del layout (página).
     * Ruta: GET /reports/debtors/view
     */
    public function debtorsPage(Request $request)
    {
        $data = $this->fetchDebtorsData($request);
        return view('reports.debtors_page', $data);
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
        $data = $this->fetchPaymentsByStudentData($request);
        return view('reports.payments_by_student', $data);
    }

    public function paymentsByStudentPdf(Request $request)
    {
        $data = $this->fetchPaymentsByStudentData($request);
        return $this->renderPdfFromView('reports.payments_by_student', $data, 'pagos_por_alumno.pdf');
    }

    /**
     * Mostrar listado de pagos por alumno dentro del layout (página).
     * Ruta: GET /reports/payments-by-student/view
     */
    public function paymentsByStudentPage(Request $request)
    {
        $data = $this->fetchPaymentsByStudentData($request);
        return view('reports.payments_by_student_page', $data);
    }

    /**
     * Fetch data for accounting report (all payments filtered by payment date range)
     */
    private function fetchAccountingData(Request $request): array
    {
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $query = Payment::with(['student', 'paymentMethod'])->orderBy('payment_date');

        if ($dateFrom && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $query->whereDate('payment_date', '>=', $dateFrom);
        }
        if ($dateTo && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $query->whereDate('payment_date', '<=', $dateTo);
        }

        $payments = $query->get();
        $total = $payments->sum('amount');

        return [
            'company'      => $this->companyName,
            'payments'     => $payments,
            'total'        => $total,
            'date_from'    => $dateFrom,
            'date_to'      => $dateTo,
            'generated_at' => Carbon::now(),
        ];
    }

    //
    // 6) Reporte contable
    //
    public function accountingForm()
    {
        return view('reports.accounting_form');
    }

    public function accountingPage(Request $request)
    {
        $data = $this->fetchAccountingData($request);
        return view('reports.accounting_page', $data);
    }

    /**
     * Export accounting report to CSV (opens natively in Excel).
     */
    public function accountingExcel(Request $request)
    {
        $data = $this->fetchAccountingData($request);
        $payments = $data['payments'];
        $total    = $data['total'];
        $dateFrom = $data['date_from'];
        $dateTo   = $data['date_to'];

        $filename = 'reporte_contable';
        if ($dateFrom) $filename .= '_desde_' . $dateFrom;
        if ($dateTo)   $filename .= '_hasta_' . $dateTo;
        $filename .= '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($payments, $total, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM so Excel recognises encoding
            fputs($file, "\xEF\xBB\xBF");

            // Title rows
            fputcsv($file, ['Reporte Contable – Punto Natación']);
            if ($dateFrom || $dateTo) {
                $period = 'Período: ' . ($dateFrom ? $dateFrom : '—') . ' al ' . ($dateTo ? $dateTo : '—');
                fputcsv($file, [$period]);
            }
            fputcsv($file, ['Generado: ' . Carbon::now()->format('d/m/Y H:i')]);
            fputcsv($file, []);

            // Column headers
            fputcsv($file, ['Alumno', 'DNI', 'Fecha de Pago', 'Monto', 'Medio de Pago']);

            // Data rows
            foreach ($payments as $p) {
                fputcsv($file, [
                    $p->student->name ?? '—',
                    $p->student->dni  ?? '—',
                    $p->payment_date  ? Carbon::parse($p->payment_date)->format('d/m/Y') : '—',
                    number_format((float) $p->amount, 2, '.', ''),
                    $p->paymentMethod->name ?? 'N/A',
                ]);
            }

            // Total row
            fputcsv($file, []);
            fputcsv($file, ['TOTAL', '', '', number_format((float) $total, 2, '.', ''), '']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function allStudents(Request $request)
    {
        $data = $this->fetchAllStudentsData($request);
        return view('reports.all_students', $data);
    }

    public function allStudentsPdf(Request $request)
    {
        $data = $this->fetchAllStudentsData($request);
        return $this->renderPdfFromView('reports.all_students', $data, 'todos_los_alumnos.pdf');
    }

    public function allStudentsPage(Request $request)
    {
        $data = $this->fetchAllStudentsData($request);
        return view('reports.all_students_page', $data);
    }
}