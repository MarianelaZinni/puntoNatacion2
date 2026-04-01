<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query  = User::with(['teacher', 'students'])
                      ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%"))
                      ->orderBy('name');

        $users = $query->paginate(15)->withQueryString();
        $roles = User::roles();

        return view('users.index', compact('users', 'search', 'roles'));
    }

    public function create()
    {
        $roles    = User::roles();
        $teachers = Teacher::orderBy('name')->get();
        $students = Student::orderBy('name')->get();

        return view('users.create', compact('roles', 'teachers', 'students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => ['required', 'confirmed', Password::min(8)],
            'role'       => 'required|in:admin,enfermeria,alumno,profesor',
            'teacher_id' => 'nullable|exists:teachers,id',
            'student_ids'=> 'nullable|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'role'              => $data['role'],
            'teacher_id'        => ($data['role'] === 'profesor') ? ($data['teacher_id'] ?? null) : null,
            'email_verified_at' => now(),
        ]);

        if ($data['role'] === 'alumno' && ! empty($data['student_ids'])) {
            $user->students()->sync($data['student_ids']);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles    = User::roles();
        $teachers = Teacher::orderBy('name')->get();
        $students = Student::orderBy('name')->get();
        $linkedStudentIds = $user->students->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'teachers', 'students', 'linkedStudentIds'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'role'       => 'required|in:admin,enfermeria,alumno,profesor',
            'teacher_id' => 'nullable|exists:teachers,id',
            'student_ids'=> 'nullable|array',
            'student_ids.*' => 'exists:students,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(8)];
        }

        $data = $request->validate($rules);

        $user->name       = $data['name'];
        $user->email      = $data['email'];
        $user->role       = $data['role'];
        $user->teacher_id = ($data['role'] === 'profesor') ? ($data['teacher_id'] ?? null) : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if ($data['role'] === 'alumno') {
            $user->students()->sync($data['student_ids'] ?? []);
        } else {
            $user->students()->detach();
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->guard()->user()->id) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->students()->detach();
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}