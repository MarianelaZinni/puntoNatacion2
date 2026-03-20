<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', null);
        $allowedSorts = ['id', 'dni', 'name', 'email'];
        $sort = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'id';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->query('per_page', 10);

        $query = Teacher::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sort, $direction);

        try {
            $teachers = $query->paginate($perPage)->withQueryString();

            if ($request->ajax() || $request->wantsJson()) {
                $rowsHtml = view('teachers.partials.rows', compact('teachers'))->render();
                $paginationHtml = view('teachers.partials.pagination', compact('teachers'))->render();

                return response()->json([
                    'rows' => $rowsHtml,
                    'pagination' => $paginationHtml,
                ]);
            }

            return view('teachers.index', compact('teachers', 'search', 'sort', 'direction'));
        } catch (\Throwable $e) {
            Log::error('Error en TeacherController@index: '.$e->getMessage(), [
                'exception' => $e,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Error al obtener profesores: '.$e->getMessage()
                ], 500);
            }

            throw $e;
        }
    }

    public function create()
    {
        return view('teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'nullable|string|max:20|unique:teachers,dni',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:1000',
        ]);

        Teacher::create($request->only('name', 'dni', 'email', 'phone', 'address', 'observations'));

        return redirect()->route('teachers.index')
                         ->with('success', 'Profesor creado correctamente.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load(['titularSubjects.subjectType', 'suplenteSubjects.subjectType']);

        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        // Load the teacher's currently assigned subjects (titular + suplente)
        $teacher->load(['titularSubjects.subjectType', 'suplenteSubjects.subjectType']);

        // Available subjects: those where at least one slot is free OR already held by this teacher.
        // Specifically: classes that have no teacher at all, OR where this teacher already occupies one slot
        // (so they can also be assigned to the other slot).
        // This excludes classes where BOTH slots are taken by other teachers.
        $availableSubjects = Subject::with('subjectType')
            ->where(function ($q) use ($teacher) {
                $q->where(function ($q2) use ($teacher) {
                    // titular slot is free or belongs to this teacher
                    $q2->whereNull('titular_teacher_id')
                       ->orWhere('titular_teacher_id', $teacher->id);
                })->orWhere(function ($q2) use ($teacher) {
                    // suplente slot is free or belongs to this teacher
                    $q2->whereNull('suplente_teacher_id')
                       ->orWhere('suplente_teacher_id', $teacher->id);
                });
            })
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        return view('teachers.edit', compact('teacher', 'availableSubjects'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'nullable|string|max:20|unique:teachers,dni,'.$teacher->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:1000',
        ]);

        $teacher->update($request->only('name', 'dni', 'email', 'phone', 'address', 'observations'));

        return redirect()->route('teachers.index')
                         ->with('success', 'Profesor actualizado correctamente.');
    }

    /**
     * Assign a class to the teacher as titular or suplente.
     * Route: POST /teachers/{teacher}/assign-class
     */
    public function assignClass(Request $request, Teacher $teacher)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'role'       => 'required|in:titular,suplente',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $role    = $request->role;
        $field   = $role === 'titular' ? 'titular_teacher_id' : 'suplente_teacher_id';

        // Check slot is free or already belongs to this teacher
        if ($subject->$field !== null && $subject->$field !== $teacher->id) {
            return redirect()->route('teachers.edit', $teacher)
                ->with('error', "Esa clase ya tiene un profesor {$role} asignado.");
        }

        // Prevent assigning as both titular AND suplente of the same class
        $otherField = $role === 'titular' ? 'suplente_teacher_id' : 'titular_teacher_id';
        $otherRole  = $role === 'titular' ? 'suplente' : 'titular';
        if ($subject->$otherField === $teacher->id) {
            return redirect()->route('teachers.edit', $teacher)
                ->with('error', "El profesor ya está asignado a esa clase como {$otherRole}.");
        }

        $subject->update([$field => $teacher->id]);

        return redirect()->route('teachers.edit', $teacher)
            ->with('success', "Clase asignada correctamente como {$role}.");
    }

    /**
     * Remove the teacher from a class (either as titular or suplente).
     * Route: POST /teachers/{teacher}/unassign-class
     */
    public function unassignClass(Request $request, Teacher $teacher)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'role'       => 'required|in:titular,suplente',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $field   = $request->role === 'titular' ? 'titular_teacher_id' : 'suplente_teacher_id';

        if ($subject->$field !== $teacher->id) {
            return redirect()->route('teachers.edit', $teacher)
                ->with('error', 'El profesor no está asignado a esa clase como ' . $request->role . '.');
        }

        $subject->update([$field => null]);

        return redirect()->route('teachers.edit', $teacher)
            ->with('success', 'Clase desasignada correctamente.');
    }

    public function destroy(Teacher $teacher)
    {
        try {
            $teacher->delete();
            return redirect()->route('teachers.index')
                             ->with('success', 'Profesor eliminado correctamente.');
        } catch (QueryException $e) {
            return redirect()->route('teachers.index')
                             ->with('error', 'No se pudo eliminar el profesor. Puede estar asignado a clases.');
        } catch (\Exception $e) {
            return redirect()->route('teachers.index')
                             ->with('error', 'Ocurrió un error al intentar eliminar.');
        }
    }
}
