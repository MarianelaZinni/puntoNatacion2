<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
   public function index(Request $request)
   {
       $search = $request->query('search', null);
       $allowedSorts = ['id', 'dni', 'name', 'email'];
       $sort = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'id';
       $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
       $perPage = (int) $request->query('per_page', 10);

       $query = Student::query();

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

           if ($request->ajax() || $request->wantsJson()) {
               $rowsHtml = view('students.partials.rows', compact('students'))->render();
               $paginationHtml = view('students.partials.pagination', compact('students'))->render();

               return response()->json([
                   'rows' => $rowsHtml,
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
            'email' => 'required|email|unique:students,email',
            'address' => 'nullable',
            'phone' => 'nullable',
        ]);
        Student::create($request->only('dni', 'name', 'email', 'address', 'phone'));
        return redirect()->route('students.index')->with('success', 'Alumno creado correctamente.');
    }

    /**
     * Show (no change from before).
     */
    public function show(Student $student)
    {
        $student->load(['subjects' => function ($q) {
            $q->with('subjectType')->withCount('students')->orderBy('start_time');
        }]);

        return view('students.show', compact('student'));
    }

    /**
     * Edit: now loads all subjects (to add) and student->subjects for showing enrolled classes with actions.
     */
    public function edit(Student $student)
    {
        // Load student's enrolled subjects and their types
        $student->load(['subjects' => function ($q) {
            $q->with('subjectType')->withCount('students')->orderBy('start_time');
        }]);

        // Load all subjects to allow enrolling (with subjectType and students_count)
        $subjects = Subject::with('subjectType')->withCount('students')->orderBy('start_time')->get();

        return view('students.edit', compact('student', 'subjects'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'dni' => 'required|unique:students,dni,' . $student->id,
            'name' => 'required',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'address' => 'nullable',
            'phone' => 'nullable',
        ]);
        $student->update($request->only('dni', 'name', 'email', 'address', 'phone'));
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

        return view('students.enroll-class', compact('student', 'subjects'));
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
            return redirect()->route('students.edit', $student)->with('error', 'El alumno ya está anotado en esa materia.');
        }

        // optional: check capacity if you want to prevent overbooking
        $subject = Subject::withCount('students')->find($subjectId);
        if ($subject && $subject->capacity !== null && $subject->students_count >= $subject->capacity) {
            return redirect()->route('students.edit', $student)->with('error', 'No se puede anotar: la clase está llena.');
        }

        try {
            $student->subjects()->attach($subjectId);
            return redirect()->route('students.edit', $student)->with('success', 'Alumno anotado en clase correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error en enrollClass: '.$e->getMessage(), ['student_id' => $student->id, 'subject_id' => $subjectId]);
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
            return redirect()->route('students.edit', $student)->with('error', 'El alumno no está inscripto en esa clase.');
        }

        try {
            $student->subjects()->detach($subjectId);
            return redirect()->route('students.edit', $student)->with('success', 'Alumno desinscripto correctamente de la clase.');
        } catch (\Throwable $e) {
            Log::error('Error en unenrollClass: '.$e->getMessage(), ['student_id' => $student->id, 'subject_id' => $subjectId]);
            return redirect()->route('students.edit', $student)->with('error', 'Ocurrió un error al desinscribir al alumno.');
        }
    }
}