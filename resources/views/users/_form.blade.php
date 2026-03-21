{{-- Shared form fields for create/edit user --}}
<div class="grid grid-cols-1 gap-4">

    {{-- Name --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Nombre <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name"
               value="{{ old('name', $user?->name) }}"
               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
               required>
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Email <span class="text-red-500">*</span>
        </label>
        <input type="email" name="email" id="email"
               value="{{ old('email', $user?->email) }}"
               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
               required>
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Contraseña {{ $user ? '(dejar vacío para no cambiar)' : '' }} {{ !$user ? '*' : '' }}
        </label>
        <input type="password" name="password" id="password"
               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
               {{ !$user ? 'required' : '' }}
               autocomplete="new-password">
        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Password confirmation --}}
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Confirmar Contraseña {{ !$user ? '*' : '' }}
        </label>
        <input type="password" name="password_confirmation" id="password_confirmation"
               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
               {{ !$user ? 'required' : '' }}
               autocomplete="new-password">
    </div>

    {{-- Role --}}
    <div>
        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Rol <span class="text-red-500">*</span>
        </label>
        <select name="role" id="role"
                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                required>
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" {{ old('role', $user?->role) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Teacher link (shown only for 'profesor' role) --}}
    <div id="teacher-section" class="{{ old('role', $user?->role) === 'profesor' ? '' : 'hidden' }}">
        <label for="teacher_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Vincular con Profesor
        </label>
        <select name="teacher_id" id="teacher_id"
                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]">
            <option value="">— Sin vincular —</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}"
                    {{ (int) old('teacher_id', $user?->teacher_id) === $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }}
                    @if($teacher->dni) (DNI {{ $teacher->dni }}) @endif
                </option>
            @endforeach
        </select>
        @error('teacher_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Students link (shown only for 'alumno' role) --}}
    <div id="student-section" class="{{ old('role', $user?->role) === 'alumno' ? '' : 'hidden' }}">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Vincular con Alumno(s)
        </label>
        <div class="max-h-48 overflow-y-auto border border-gray-300 dark:border-zinc-700 rounded p-2 bg-white dark:bg-zinc-800 space-y-1">
            @foreach($students as $student)
            @php
                $checked = in_array($student->id, old('student_ids', $linkedStudentIds ?? []));
            @endphp
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-700 px-1 rounded">
                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                       class="rounded border-gray-300 dark:border-zinc-600 text-[#29b1dc]"
                       {{ $checked ? 'checked' : '' }}>
                {{ $student->name }}
                @if($student->dni) <span class="text-gray-400 text-xs">(DNI {{ $student->dni }})</span> @endif
            </label>
            @endforeach
            @if($students->isEmpty())
                <p class="text-xs text-gray-400 p-1">No hay alumnos registrados.</p>
            @endif
        </div>
        @error('student_ids') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

</div>
