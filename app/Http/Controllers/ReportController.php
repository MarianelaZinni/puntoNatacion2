<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\Student;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // barryvdh/laravel-dompdf facade
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

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
     * Fetch data for class type enrollees report
     */
    private function fetchClassTypeEnrolleesData(Request $request): array
    {
        $subjectTypeId = $request->query('subject_type_id');

        $subjectTypes = SubjectType::withCount('subjects')->orderBy('description')->get();

        $subjectType = null;
        $students = collect();
        $classesCount = 0;

        if ($subjectTypeId) {
            $subjectType = SubjectType::withCount('subjects')->find($subjectTypeId);

            if ($subjectType) {
                $classesCount = (int) $subjectType->subjects_count;

                $students = Student::query()
                    ->select('students.*')
                    ->whereHas('subjects', function ($q) use ($subjectTypeId) {
                        $q->where('subject_type_id', $subjectTypeId);
                    })
                    ->with(['subjects' => function ($q) use ($subjectTypeId) {
                        $q->where('subject_type_id', $subjectTypeId)
                            ->with('subjectType')
                            ->orderBy('day')
                            ->orderBy('start_time');
                    }])
                    ->orderBy('name')
                    ->distinct()
                    ->get();
            }
        }

        return [
            'company' => $this->companyName,
            'subject_type' => $subjectType,
            'subject_types' => $subjectTypes,
            'students' => $students,
            'classes_count' => $classesCount,
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
    // 1.1) Inscriptos por tipo de clase
    //
    public function classTypeEnrolleesForm()
    {
        $subjectTypes = SubjectType::withCount('subjects')->orderBy('description')->get();
        return view('reports.class_type_enrollees_form', compact('subjectTypes'));
    }

    public function classTypeEnrollees(Request $request)
    {
        $data = $this->fetchClassTypeEnrolleesData($request);
        return view('reports.class_type_enrollees', $data);
    }

    public function classTypeEnrolleesPdf(Request $request)
    {
        $data = $this->fetchClassTypeEnrolleesData($request);
        $subjectTypeId = $request->query('subject_type_id');
        $filename = 'inscriptos_tipo_clase_' . ($subjectTypeId ?: 'all') . '.pdf';
        return $this->renderPdfFromView('reports.class_type_enrollees', $data, $filename);
    }

    public function classTypeEnrolleesPage(Request $request)
    {
        $data = $this->fetchClassTypeEnrolleesData($request);
        return view('reports.class_type_enrollees_page', $data);
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
     * Export accounting report to Excel (.xlsx).
     */
    public function accountingExcel(Request $request)
    {
        $data     = $this->fetchAccountingData($request);
        $payments = $data['payments'];
        $total    = $data['total'];
        $dateFrom = $data['date_from'];
        $dateTo   = $data['date_to'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Contable');

        // ── Title block ──────────────────────────────────────────────────────
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'Reporte Contable – Punto Natación');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        if ($dateFrom || $dateTo) {
            $sheet->mergeCells("A{$row}:E{$row}");
            $period = 'Período: ' . ($dateFrom ?: '—') . ' al ' . ($dateTo ?: '—');
            $sheet->setCellValue("A{$row}", $period);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", 'Generado: ' . Carbon::now()->format('d/m/Y H:i'));
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row += 2; // blank row

        // ── Column headers ────────────────────────────────────────────────────
        $headerRow = $row;
        $headers = ['Alumno', 'DNI', 'Fecha de Pago', 'Monto', 'Medio de Pago'];
        foreach ($headers as $col => $label) {
            $cell = chr(65 + $col) . $headerRow;
            $sheet->setCellValue($cell, $label);
        }
        $headerRange = "A{$headerRow}:E{$headerRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color())->setRGB('FFFFFF')
        );
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E40AF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        // ── Data rows ─────────────────────────────────────────────────────────
        foreach ($payments as $p) {
            $sheet->setCellValue("A{$row}", $p->student->name ?? '—');
            $sheet->setCellValue("B{$row}", $p->student->dni  ?? '—');
            $sheet->setCellValue("C{$row}", $p->payment_date
                ? Carbon::parse($p->payment_date)->format('d/m/Y')
                : '—');
            $sheet->setCellValue("D{$row}", (float) $p->amount);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue("E{$row}", $p->paymentMethod->name ?? 'N/A');

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:E{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EFF6FF');
            }
            $row++;
        }

        // ── Total row ─────────────────────────────────────────────────────────
        $row++;
        $sheet->setCellValue("C{$row}", 'TOTAL');
        $sheet->setCellValue("D{$row}", (float) $total);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("C{$row}:E{$row}")->getFont()->setBold(true);
        $sheet->getStyle("C{$row}:E{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DBEAFE');

        // ── Column widths ─────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(20);

        // ── Filename ──────────────────────────────────────────────────────────
        $filename = 'reporte_contable';
        if ($dateFrom) $filename .= '_desde_' . $dateFrom;
        if ($dateTo)   $filename .= '_hasta_' . $dateTo;
        $filename .= '.xlsx';

        // ── Stream to browser ─────────────────────────────────────────────────
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
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