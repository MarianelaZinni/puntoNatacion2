<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\QueryException;
//use App\Models\Pago;
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
        // Logueamos el error completo
        Log::error('Error en StudentController@index: '.$e->getMessage(), [
            'exception' => $e,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => $perPage,
        ]);

        // Si es petición AJAX, devolvemos JSON con el mensaje (útil para debug en frontend)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'Error al obtener estudiantes: '.$e->getMessage()
            ], 500);
        }

        // para peticiones normales, re-lanzamos la excepción para que Laravel la maneje (o redirigimos)
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

    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }


     public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'dni' => 'required|unique:students,dni,' . $student->id,
            'name' => 'required',
            'email' => 'required|email|unique:students,eail,' . $student->id,
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


     public function enrollClassForm(Student $student)
    {
        $subjects = Subject::all();
        return view('students.enroll-class', compact('student', 'subjects'));
    }

    public function enrollClass(Request $request, Student $student)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);
        // evitar duplicados
        if ($student->subjects()->where('subject_id', $request->subject_id)->exists()) {
            return redirect()->back()->withErrors(['subject_id' => 'El alumno ya está anotado en esa materia.']);
        }
        $student->subjects()->attach($request->subject_id);
        return redirect()->route('students.index')->with('success', 'Alumno anotado en clase correctamente.');
    }

    /*public function registerPaymentForm(Student $student)
    {
        return view('students.register-payment', compact('student'));
    }*/

    /*public function registerPayment(Request $request, Student $student)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
        ]);
        Payment::create([
            'student_id' => $student->id,
            'amount' => $request->amount,
            'date' => $request->date,
        ]);
        return redirect()->route('students.index')->with('success', 'Pago registrado correctamente.');
    }*/
}