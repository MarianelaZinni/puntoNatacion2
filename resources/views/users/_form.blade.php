@php
    $isCreate = ! $user;
    $selectedRole = old('role', $user?->role);
    $selectedStudentIds = collect(old('student_ids', $linkedStudentIds ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $isStudentLinkedRole = in_array($selectedRole, ['alumno', 'super_alumno'], true);
    $shouldLockDataFields = $isCreate && (
        blank($selectedRole) ||
        ($isStudentLinkedRole && count($selectedStudentIds) === 0)
    );
@endphp

<div class="grid grid-cols-1 gap-4">
    <div>
        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Rol <span class="text-red-500">*</span>
        </label>
        <select
            name="role"
            id="role"
            class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
            required
        >
            @if($isCreate)
                <option value="">— Seleccionar rol —</option>
            @endif
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" {{ $selectedRole === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div id="teacher-section" class="{{ $selectedRole === 'profesor' ? '' : 'hidden' }}">
        <label for="teacher_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Vincular con Profesor
        </label>
        <select
            name="teacher_id"
            id="teacher_id"
            class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
        >
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

    <div id="student-section" class="{{ $isStudentLinkedRole ? '' : 'hidden' }}">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Vincular con Alumno(s) <span class="text-red-500">*</span>
        </label>
        @if($isCreate)
            <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                Seleccioná primero los alumnos vinculados para habilitar los datos del usuario.
            </p>
        @endif
        <div class="max-h-48 overflow-y-auto border border-gray-300 dark:border-zinc-700 rounded p-2 bg-white dark:bg-zinc-800 space-y-1">
            @foreach($students as $student)
                @php
                    $checked = in_array($student->id, $selectedStudentIds, true);
                @endphp
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-700 px-1 rounded">
                    <input
                        type="checkbox"
                        name="student_ids[]"
                        value="{{ $student->id }}"
                        class="rounded border-gray-300 dark:border-zinc-600 text-[#29b1dc]"
                        data-student-checkbox
                        {{ $checked ? 'checked' : '' }}
                    >
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

    <div id="user-data-section" class="space-y-4 {{ $shouldLockDataFields ? 'opacity-60' : '' }}">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nombre <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name', $user?->name) }}"
                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                data-user-input
                @disabled($shouldLockDataFields)
                required
            >
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Email <span id="email-required-indicator" class="text-red-500 {{ $isStudentLinkedRole ? 'hidden' : '' }}">*</span>
            </label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email', $user?->email) }}"
                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                data-user-input
                @disabled($shouldLockDataFields)
                {{ $isStudentLinkedRole ? '' : 'required' }}
            >
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div id="dni-field" class="{{ $isStudentLinkedRole ? '' : 'hidden' }}">
            <label for="dni" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                DNI
            </label>
            <input
                type="text"
                name="dni"
                id="dni"
                value="{{ old('dni', $user?->dni) }}"
                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                data-user-input
                @disabled($shouldLockDataFields)
            >
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Para usuarios vinculados a alumnos, completá email o DNI.
            </p>
            @error('dni') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Contraseña {{ $user ? '(dejar vacío para no cambiar)' : '*' }}
            </label>
            <input
                type="password"
                name="password"
                id="password"
                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                data-user-input
                @disabled($shouldLockDataFields)
                {{ !$user ? 'required' : '' }}
                autocomplete="new-password"
            >
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Confirmar Contraseña {{ !$user ? '*' : '' }}
            </label>
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                data-user-input
                @disabled($shouldLockDataFields)
                {{ !$user ? 'required' : '' }}
                autocomplete="new-password"
            >
        </div>
    </div>
</div>
