<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectPrice;
use App\Models\AttendanceRecord;
use App\Services\PriceCalculator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StudentController extends Controller
{
   public function index(Request $request)
   {
       $search = $request->query('search', null);
       $allowedSorts = ['id', 'dni', 'name', 'email'];
       $sort = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'id';
       $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
       $perPage = max(1, (int) $request->query('per_page', 10));

       $query = Student::query();

       // eager load subjects (with subjectType) and payments to compute deuda
       $query->with([
           'subjects' => function ($q) {
               $q->with('subjectType')->withCount('students')->orderBy('start_time');
           },
           'payments',
           'pauses',
       ]);

       if ($search) {
           $query->where(function ($q) use ($search) {
               $q->where('name', 'like', "%{$search}%")
                 ->orWhere('dni', 'like', "%{$search}%")
                 ->orWhere('email', 'like', "%{$search}%");
           });
       }

       $query->orderBy($sort, $direction);

       try {
           $students = $query->paginate($perPage)->withQueryString();

           // calcular deuda y bandera paid_this_month para cada alumno en la colección paginada
           $students->getCollection()->transform(function ($student) {
               // Usar el helper del modelo que calcula la deuda desde la creación usando el monto mensual actual.
               $calc = $student->calculateDebtFromCreationUsingCurrentMonthly();

               $student->debt = $calc['debt'];
               $student->monthly_amount = $calc['monthly_amount'];
               $student->unpaid_periods = $calc['unpaid_periods'];
               $student->selectable_periods = $calc['selectable_periods'] ?? [];
               $student->next_unpaid_period = $calc['next_unpaid_period'];
               $student->has_debt = ($calc['debt'] > 0);

               // Un periodo se considera pagado si existe al menos un registro de pago,
               // independientemente del monto. Usamos el helper del modelo que implementa
               // esta regla de negocio de manera consistente.
               $nowYm = Carbon::now()->format('Y-m');
               $student->paid_this_month = $student->isPeriodFullyPaid($nowYm);

               // Determinar estado (status) según las reglas:
               // - "deudor"   => si debe algún mes anterior OR (estamos > dia 10 y no pagó el mes en curso)
               // - "pendiente"=> si estamos <= dia 10 y no pagó el mes en curso y no debe meses anteriores
               // - "al_dia"   => si pagó todos los periodos anteriores y el mes actual
               //
               // Regla simplificada aplicada:
               // 1) Si monthly_amount <= 0 => 'al_dia' (no corresponde deuda)
               // 2) else if has previous unpaid months => 'deudor'
               // 3) else if paid_this_month => 'al_dia'
               // 4) else if today > 10 => 'deudor' (mes actual vencido)
               // 5) else => 'pendiente' (antes del día 11, sin deuda previa)
               $unpaidPeriods = is_array($student->unpaid_periods) ? $student->unpaid_periods : [];
               $hasPreviousUnpaid = collect($unpaidPeriods)->contains(function ($p) use ($nowYm) {
                   return ($p['period'] ?? '') !== $nowYm;
               });
               $todayDay = Carbon::now()->day;

               if (empty($student->monthly_amount) || (float)$student->monthly_amount <= 0) {
                   $student->payment_status = 'al_dia';
               } elseif ($hasPreviousUnpaid) {
                   $student->payment_status = 'deudor';
               } elseif ($student->paid_this_month) {
                   $student->payment_status = 'al_dia';
               } elseif ($todayDay > 10) {
                   $student->payment_status = 'deudor';
               } else {
                   $student->payment_status = 'pendiente';
               }

                // Pre-compute can_pay flag to avoid duplicating this logic in blade partials.
               $debt       = isset($student->debt) ? (float)$student->debt : 0.0;
               $hasUnpaid  = !empty($student->unpaid_periods) && is_array($student->unpaid_periods) && count($student->unpaid_periods) > 0;
               $status     = $student->payment_status;
               $student->can_pay = !(
                   ($student->paid_this_month && !$hasUnpaid) ||
                   ($debt <= 0 && !$hasUnpaid && $status !== 'pendiente')
               );
               
               return $student;
           });

           if ($request->ajax() || $request->wantsJson()) {
               $rowsHtml = view('students.partials.rows', compact('students'))->render();
               $cardsHtml = view('students.partials.cards', compact('students'))->render();
               $paginationHtml = view('students.partials.pagination', compact('students'))->render();

               return response()->json([
                   'rows' => $rowsHtml,
                    'cards' => $cardsHtml,
                   'pagination' => $paginationHtml,
               ]);
           }

           return view('students.index', compact('students', 'search', 'sort', 'direction'));
       } catch (\Throwable $e) {
           Log::error('Error en StudentController@index: '.$e->getMessage(), [
               'exception' => $e,
               'search' => $search,
               'sort' => $sort,
               'direction' => $direction,
               'per_page' => $perPage,
           ]);

           if ($request->ajax() || $request->wantsJson()) {
               return response()->json([
                   'error' => 'Error al obtener estudiantes: '.$e->getMessage()
               ], 500);
           }

           throw $e;
       }
   }

   public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
        'dni' => 'required|unique:students,dni',
        'name' => 'required',
        'email' => 'nullable|email',
        'address' => 'nullable',
        'phone' => 'nullable',
        'observations' => 'nullable|string|max:1000',
        'birth_date' => 'nullable|date|before:today',
        'active_from' => 'required|date_format:Y-m',
    ]);

    // Normalize active_from (YYYY-MM) to first day of month for DB storage
    $data = $request->only('dni', 'name', 'email', 'address', 'phone', 'observations', 'birth_date');
    $data['active_from'] = $request->input('active_from') . '-01';

    // Crear el estudiante
    $student = Student::create($data);

    // Redireccionar a la página de inscripción de clases
    return redirect()->route('students.enrollClassForm', ['student' => $student->id])
                     ->with('success', 'Alumno creado correctamente. Ahora puedes inscribirlo a clases.');

    }

     /**
     * Show (modificado para incluir el costo estimado según las clases inscritas
     * y el historial de pagos del alumno).
     */
    public function show(Student $student)
    {
        // Cargar subjects del alumno con su subjectType y students_count
        $student->load([
            'subjects' => function ($q) {
                $q->with('subjectType')->withCount('students')->orderBy('start_time');
            },
            // Cargamos payments con paymentMethod ordenados por fecha descendente
            'payments' => function ($q) {
                $q->with('paymentMethod')->orderByDesc('payment_date');
            },
            'pauses',
        ]);

        // Calculamos el resumen de precios usando el servicio PriceCalculator
        $priceSummary = (new PriceCalculator())->calculate($student->subjects);

        // Comprobamos si el alumno ya tiene al menos un pago en el mes en curso
        $nowYm = Carbon::now()->format('Y-m');
        $paidThisMonth = $student->payments->first(function ($p) use ($nowYm) {
            $d = $p->payment_date;
            if ($d instanceof \Carbon\Carbon) {
                $ym = $d->format('Y-m');
            } else {
                try {
                    $ym = Carbon::parse($d)->format('Y-m');
                } catch (\Throwable $e) {
                    return false;
                }
            }
            return $ym === $nowYm;
        }) !== null;

        // Exponer info adicional en el objeto student para la vista
        $student->paid_this_month = $paidThisMonth;
        $student->total_paid = $student->payments->sum('amount');

        // También preparamos el mapping de precios por defecto para JS/uso futuro (opcional)
        $subjectPricesForJs = [
            'teacher' => [],
            'no_teacher' => [],
        ];
        $defaults = SubjectPrice::whereNull('subject_type_id')->get();
        foreach ($defaults as $row) {
            if ($row->has_teacher) {
                $subjectPricesForJs['teacher'][(int)$row->times_per_week] = (float)$row->price;
            } else {
                $subjectPricesForJs['no_teacher'][(int)$row->times_per_week] = (float)$row->price;
            }
        }

        // Load last 10 attendance records for this student across all their subjects
        $recentAttendance = AttendanceRecord::where('student_id', $student->id)
            ->with(['attendanceList.subject.subjectType'])
            ->join('attendance_lists', 'attendance_records.attendance_list_id', '=', 'attendance_lists.id')
            ->orderByDesc('attendance_lists.date')
            ->orderByDesc('attendance_records.id')
            ->select('attendance_records.*')
            ->limit(10)
            ->get();

        return view('students.show', compact('student', 'priceSummary', 'subjectPricesForJs', 'recentAttendance'));
    }

    /**
     * Edit: now loads all subjects (to add) and student->subjects for showing enrolled classes with actions.
     */
    public function edit(Student $student)
    {
        // Load student's enrolled subjects with subjectType and students_count
        $student->load(['subjects' => function ($q) {
            $q->with('subjectType')->withCount('students')->orderBy('start_time');
        }]);

        // Load all subjects to allow enrolling (with subjectType and students_count)
        $subjects = Subject::with('subjectType')->withCount('students')->orderBy('start_time')->get();

        // Compute current price summary for the student (uses PriceCalculator rules)
        $priceSummary = (new PriceCalculator())->calculate($student->subjects);

        // Prepare subject_prices defaults mapping for potential client-side previews (optional)
        $subjectPricesForJs = [
            'teacher' => [],
            'no_teacher' => [],
        ];
        $defaults = SubjectPrice::whereNull('subject_type_id')->get();
        foreach ($defaults as $row) {
            if ($row->has_teacher) {
                $subjectPricesForJs['teacher'][(int)$row->times_per_week] = (float)$row->price;
            } else {
                $subjectPricesForJs['no_teacher'][(int)$row->times_per_week] = (float)$row->price;
            }
        }

        // Load last 10 attendance records for this student across all their subjects
        $recentAttendance = AttendanceRecord::where('student_id', $student->id)
            ->with(['attendanceList.subject.subjectType'])
            ->join('attendance_lists', 'attendance_records.attendance_list_id', '=', 'attendance_lists.id')
            ->orderByDesc('attendance_lists.date')
            ->orderByDesc('attendance_records.id')
            ->select('attendance_records.*')
            ->limit(10)
            ->get();

        return view('students.edit', compact('student', 'subjects', 'priceSummary', 'subjectPricesForJs', 'recentAttendance'));
    }


    public function update(Request $request, Student $student)
    {
        $studentHasClasses = $student->subjects()->exists();

        $request->validate([
            'dni' => 'required|unique:students,dni,' . $student->id,
            'name' => 'required',
            'email' => 'nullable|email',
            'address' => 'nullable',
            'phone' => 'nullable',
            'observations' => 'nullable|string|max:1000',
             'birth_date' => 'nullable|date|before:today',
            'active_from' => 'required|date_format:Y-m',
        ]);
        $data = $request->only('dni', 'name', 'email', 'address', 'phone', 'observations', 'birth_date');
        // Once a student is enrolled in classes, changing active_from would retroactively alter
        // the debt start date and invalidate existing financial records. To preserve data integrity
        // the field is locked to its current value when the student has active class enrolments.
        if ($studentHasClasses) {
            $data['active_from'] = $student->active_from ? $student->active_from->format('Y-m-d') : null;
        } else {
            $data['active_from'] = $request->input('active_from') . '-01';
        }

        $student->update($data);
        return redirect()->route('students.index')->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(Student $student)
    {
        try {
            $student->delete();
            return redirect()->route('students.index')->with('success', 'Alumno eliminado correctamente.');
        } catch (QueryException $e) {
            return redirect()->route('students.index')->with('error', 'No se pudo eliminar el alumno. Puede estar relacionado a clases o pagos.');
        } catch (\Exception $e) {
            return redirect()->route('students.index')->with('error', 'Ocurrió un error al intentar eliminar.');
        }
    }


    /**
     * Show enroll form for a student (GET).
     * Route: GET students/{student}/enroll
     */
    public function enrollClassForm(Student $student)
    {
        // load all subjects with subjectType and students_count (so view shows capacity / enrolled)
        $subjects = Subject::with('subjectType')->withCount('students')->orderBy('start_time')->get();

        // also load student's current subjects to mark already enrolled ones in the view
        $student->load('subjects');

        // Compute initial price summary using PriceCalculator (counts classes con/sin profe)
        $priceSummary = (new PriceCalculator())->calculate($student->subjects);

        // Provide subject_prices defaults to JS in a simple mapping:
        // { teacher: {1:price, 2:price, ...}, no_teacher: {1:price, ...} }
        $subjectPricesForJs = [
            'teacher' => [],
            'no_teacher' => [],
        ];
        $defaults = SubjectPrice::whereNull('subject_type_id')->get();
        foreach ($defaults as $row) {
            if ($row->has_teacher) {
                $subjectPricesForJs['teacher'][(int)$row->times_per_week] = (float)$row->price;
            } else {
                $subjectPricesForJs['no_teacher'][(int)$row->times_per_week] = (float)$row->price;
            }
        }

        return view('students.enroll-class', compact('student', 'subjects', 'priceSummary'))
            ->with('subjectPricesForJs', $subjectPricesForJs);
    }
    
    /**
     * Enroll a student into a subject (class).
     * Route: POST students/{student}/enroll  (name: students.enroll)
     */
    public function enrollClass(Request $request, Student $student)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subjectId = (int) $request->input('subject_id');

        // avoid duplicate enrollments
        if ($student->subjects()->where('subject_id', $subjectId)->exists()) {
            // If AJAX / fetch, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'El alumno ya está anotado en esa materia.'], 422);
            }
            return redirect()->route('students.edit', $student)->with('error', 'El alumno ya está anotado en esa materia.');
        }

        // optional: check capacity if you want to prevent overbooking
        $subject = Subject::withCount('students')->find($subjectId);
        if ($subject && $subject->capacity !== null && $subject->students_count >= $subject->capacity) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede anotar: la clase está llena.'], 422);
            }
            return redirect()->route('students.edit', $student)->with('error', 'No se puede anotar: la clase está llena.');
        }

        try {
            $student->subjects()->attach($subjectId);

            // reload student's subjects to compute new summary
            $student->load(['subjects' => function ($q) {
                $q->with('subjectType')->withCount('students')->orderBy('start_time');
            }]);

            $newSummary = (new PriceCalculator())->calculate($student->subjects);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'priceSummary' => $newSummary]);
            }

            return redirect()->route('students.edit', $student)->with('success', 'Alumno anotado en clase correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error en enrollClass: '.$e->getMessage(), ['student_id' => $student->id, 'subject_id' => $subjectId]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Ocurrió un error al anotar al alumno.'], 500);
            }
            return redirect()->route('students.edit', $student)->with('error', 'Ocurrió un error al anotar al alumno.');
        }
    }

    /**
     * Unenroll a student from a subject (class).
     * Route: POST students/{student}/unenroll  (name: students.unenroll)
     */
    public function unenrollClass(Request $request, Student $student)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subjectId = (int) $request->input('subject_id');

        if (! $student->subjects()->where('subject_id', $subjectId)->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'El alumno no está inscripto en esa clase.'], 422);
            }
            return redirect()->route('students.edit', $student)->with('error', 'El alumno no está inscripto en esa clase.');
        }

        try {
            $student->subjects()->detach($subjectId);

            // reload student's subjects to compute new summary
            $student->load(['subjects' => function ($q) {
                $q->with('subjectType')->withCount('students')->orderBy('start_time');
            }]);

            $newSummary = (new PriceCalculator())->calculate($student->subjects);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Alumno desinscripto correctamente de la clase.',
                    'priceSummary' => $newSummary,
                    'subject_id' => $subjectId,
                ]);
            }

            return redirect()->route('students.edit', $student)->with('success', 'Alumno desinscripto correctamente de la clase.');
        } catch (\Throwable $e) {
            Log::error('Error en unenrollClass: '.$e->getMessage(), ['student_id' => $student->id, 'subject_id' => $subjectId]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Ocurrió un error al desinscribir al alumno.'], 500);
            }
            return redirect()->route('students.edit', $student)->with('error', 'Ocurrió un error al desinscribir al alumno.');
        }
    }
}
