<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query  = User::with(['teacher', 'students'])
                      ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%")
                                                    ->orWhere('dni', 'like', "%{$search}%"))
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
        $data = $this->validateUser($request, requirePassword: true);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'] ?? null,
            'dni'               => $data['dni'] ?? null,
            'password'          => Hash::make($data['password']),
            'role'              => $data['role'],
            'teacher_id'        => ($data['role'] === 'profesor') ? ($data['teacher_id'] ?? null) : null,
            'email_verified_at' => filled($data['email'] ?? null) ? now() : null,
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
        $data = $this->validateUser($request, $user, $request->filled('password'));

        $user->name       = $data['name'];
        $user->email      = $data['email'] ?? null;
        $user->dni        = $data['dni'] ?? null;
        $user->role       = $data['role'];
        $user->teacher_id = ($data['role'] === 'profesor') ? ($data['teacher_id'] ?? null) : null;

        if ($user->isDirty('email')) {
            $user->email_verified_at = filled($data['email'] ?? null) ? now() : null;
        }

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

    protected function validateUser(Request $request, ?User $user = null, bool $requirePassword = false): array
    {
        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'dni'         => ['nullable', 'string', 'max:20', Rule::unique('users', 'dni')->ignore($user?->id)],
            'role'        => ['required', Rule::in(array_keys(User::roles()))],
            'teacher_id'  => ['nullable', 'exists:teachers,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        if ($requirePassword) {
            $validator->addRules([
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);
        } elseif ($request->filled('password')) {
            $validator->addRules([
                'password' => ['confirmed', Password::min(8)],
            ]);
        }

        $validator->after(function ($validator) use ($request) {
            $role = $request->input('role');
            $email = trim((string) $request->input('email'));
            $dni = trim((string) $request->input('dni'));
            $studentIds = array_filter((array) $request->input('student_ids', []));

            if ($role === User::ROLE_ALUMNO) {
                if ($email === '' && $dni === '') {
                    $message = 'Completá al menos email o DNI.';
                    $validator->errors()->add('email', $message);
                    $validator->errors()->add('dni', $message);
                }

                if ($studentIds === []) {
                    $validator->errors()->add('student_ids', 'Seleccioná al menos un alumno para vincular.');
                }

                return;
            }

            if ($email === '') {
                $validator->errors()->add('email', 'El campo email es obligatorio.');
            }
        });

        $data = $validator->validate();
        $data['email'] = filled($data['email'] ?? null) ? $data['email'] : null;
        $data['dni'] = filled($data['dni'] ?? null) ? $data['dni'] : null;

        return $data;
    }
}