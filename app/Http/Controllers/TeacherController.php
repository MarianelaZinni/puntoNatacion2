<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    /**
     * Mostrar listado de profesores
     */
    public function index(Request $request)
    {
        $search = $request->query('search', null);
        $allowedSorts = ['id', 'name', 'surname', 'email'];
        $sort = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'id';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->query('per_page', 10);

        $query = Teacher::query();

        // Eager load subjects para mostrar cuántas clases dicta cada profesor
        $query->withCount('subjects');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sort, $direction);

        $teachers = $query->paginate($perPage)->withQueryString();

        return view('teachers.index', compact('teachers', 'search', 'sort', 'direction', 'perPage'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('teachers.create');
    }

    /**
     * Guardar nuevo profesor
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'birth_date' => 'nullable|date',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'surname.required' => 'El apellido es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('teachers.create')
                ->withErrors($validator)
                ->withInput();
        }

        $teacher = Teacher::create($request->all());

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Profesor creado exitosamente.');
    }

    /**
     * Mostrar detalle de un profesor
     */
    public function show(Teacher $teacher)
    {
        // Eager load subjects with subjectType, students, student notes, and comments (titular y suplente)
        $teacher->load([
            'subjects.subjectType',
            'subjects.students',
            'subjects.studentNotes.teacher',
            'subjects.studentNotes.student',
            'subjects.comments.teacher',
            'subjectsAsSubstitute.subjectType',
            'subjectsAsSubstitute.students',
            'subjectsAsSubstitute.studentNotes.teacher',
            'subjectsAsSubstitute.studentNotes.student',
            'subjectsAsSubstitute.comments.teacher'
        ]);
        
        // Obtener todas las clases disponibles (que no tengan a este profesor como titular ni suplente)
        $availableSubjects = Subject::with(['subjectType', 'teacher', 'substituteTeacher'])
            ->where(function($query) use ($teacher) {
                $query->where('teacher_id', '!=', $teacher->id)
                      ->orWhereNull('teacher_id');
            })
            ->where(function($query) use ($teacher) {
                $query->where('substitute_teacher_id', '!=', $teacher->id)
                      ->orWhereNull('substitute_teacher_id');
            })
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        return view('teachers.show', compact('teacher', 'availableSubjects'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Teacher $teacher)
    {
        return view('teachers.edit', compact('teacher'));
    }

    /**
     * Actualizar profesor
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'birth_date' => 'nullable|date',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'surname.required' => 'El apellido es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('teachers.edit', $teacher)
                ->withErrors($validator)
                ->withInput();
        }

        $teacher->update($request->all());

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Profesor actualizado exitosamente.');
    }

    /**
     * Eliminar profesor
     */
    public function destroy(Teacher $teacher)
    {
        // Al eliminar el profesor, las clases quedan sin profesor (teacher_id = NULL) gracias a nullOnDelete
        $teacher->delete();

        return redirect()->route('teachers.index')
            ->with('success', 'Profesor eliminado exitosamente.');
    }

    /**
     * Asignar profesor a una clase (como titular o suplente)
     */
    public function assignToSubject(Request $request, Teacher $teacher)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'role' => 'required|in:titular,substitute',
        ]);

        if ($validator->fails()) {
            return redirect()->route('teachers.show', $teacher)
                ->withErrors($validator);
        }

        $subject = Subject::findOrFail($request->subject_id);
        
        if ($request->role === 'titular') {
            $subject->teacher_id = $teacher->id;
            $message = 'Profesor asignado como titular exitosamente.';
        } else {
            $subject->substitute_teacher_id = $teacher->id;
            $message = 'Profesor asignado como suplente exitosamente.';
        }
        
        $subject->save();

        return redirect()->route('teachers.show', $teacher)
            ->with('success', $message);
    }

    /**
     * Remover profesor de una clase
     */
    public function removeFromSubject(Request $request, Teacher $teacher)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'role' => 'required|in:titular,substitute',
        ]);

        if ($validator->fails()) {
            return redirect()->route('teachers.show', $teacher)
                ->withErrors($validator);
        }

        $subject = Subject::findOrFail($request->subject_id);
        
        // Verificar que el profesor esté asignado en el rol especificado
        if ($request->role === 'titular' && $subject->teacher_id === $teacher->id) {
            $subject->teacher_id = null;
            $message = 'Profesor removido como titular exitosamente.';
        } elseif ($request->role === 'substitute' && $subject->substitute_teacher_id === $teacher->id) {
            $subject->substitute_teacher_id = null;
            $message = 'Profesor removido como suplente exitosamente.';
        } else {
            return redirect()->route('teachers.show', $teacher)
                ->withErrors(['error' => 'El profesor no está asignado en ese rol para esta clase.']);
        }
        
        $subject->save();

        return redirect()->route('teachers.show', $teacher)
            ->with('success', $message);
    }
}
