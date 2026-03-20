<?php

namespace App\Http\Controllers;

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
        return view('teachers.edit', compact('teacher'));
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
