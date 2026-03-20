<x-layouts.app title="Editar Profesor">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar Profesor</h1>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 text-red-800 dark:text-red-200">
                <p class="font-semibold mb-2">Se encontraron los siguientes errores:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('teachers.update', $teacher) }}" method="POST" id="teacher-form" class="space-y-8 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Nombre --}}
                <div>
                    <label for="name" class="block text-base font-medium text-gray-700 dark:text-gray-300">Nombre completo <span class="text-red-500">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        value="{{ old('name', $teacher->name) }}"
                        placeholder="Ej. Juan García"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('name') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DNI --}}
                <div>
                    <label for="dni" class="block text-base font-medium text-gray-700 dark:text-gray-300">DNI / Documento</label>
                    <input
                        id="dni"
                        name="dni"
                        type="text"
                        value="{{ old('dni', $teacher->dni) }}"
                        placeholder="Ej. 12345678"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('dni') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('dni') ? 'true' : 'false' }}"
                    >
                    @error('dni')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-base font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $teacher->email) }}"
                        placeholder="ejemplo@dominio.com"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('email') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label for="phone" class="block text-base font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone', $teacher->phone) }}"
                        placeholder="+54 9 11 1234 5678"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('phone') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div class="sm:col-span-2">
                    <label for="address" class="block text-base font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                    <input
                        id="address"
                        name="address"
                        type="text"
                        value="{{ old('address', $teacher->address) }}"
                        placeholder="Calle, número, ciudad"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('address') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                    >
                    @error('address')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Observaciones --}}
                <div class="sm:col-span-2">
                    <label for="observations" class="block text-base font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                    <textarea
                        id="observations"
                        name="observations"
                        rows="3"
                        placeholder="Notas adicionales sobre el profesor..."
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('observations') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('observations') ? 'true' : 'false' }}"
                    >{{ old('observations', $teacher->observations) }}</textarea>
                    @error('observations')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="sm:col-span-2 flex items-center justify-end gap-3">
                    <a href="{{ route('teachers.index') }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500 transition">
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        id="save-btn"
                        class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition"
                    >
                        <svg id="save-spinner" class="hidden animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ============================================================ --}}
    {{-- Sección: Clases asignadas + asignar nueva clase              --}}
    {{-- ============================================================ --}}
    <div class="max-w-4xl mx-auto px-4 pb-12 space-y-6">

        {{-- Panel: Clases actualmente asignadas --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Clases asignadas</h2>

            @php
                $allAssigned = collect();
                foreach ($teacher->titularSubjects as $s) {
                    $allAssigned->push(['subject' => $s, 'role' => 'titular']);
                }
                foreach ($teacher->suplenteSubjects as $s) {
                    $allAssigned->push(['subject' => $s, 'role' => 'suplente']);
                }
                $allAssigned = $allAssigned->sortBy(fn($a) => $a['subject']->day . $a['subject']->start_time);
            @endphp

            @if($allAssigned->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No tiene clases asignadas todavía.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Día</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Horario</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Rol</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($allAssigned as $item)
                            @php $s = $item['subject']; $role = $item['role']; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $s->subjectType->description ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-200 capitalize">{{ $s->day }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ substr($s->start_time, 0, 5) }} – {{ substr($s->end_time, 0, 5) }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if($role === 'titular')
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Titular</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Suplente</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <form action="{{ route('teachers.unassignClass', $teacher) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="subject_id" value="{{ $s->id }}">
                                        <input type="hidden" name="role" value="{{ $role }}">
                                        <button type="submit"
                                                onclick="return confirm('¿Desasignar esta clase?')"
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded text-xs text-white bg-red-500 hover:bg-red-600 transition">
                                            <flux:icon name="x-mark" class="h-3 w-3" />
                                            Desasignar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Panel: Asignar nueva clase --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Asignar clase</h2>

            @php
                // Already-assigned subject IDs per role (to disable options in the dropdown)
                $titularIds   = $teacher->titularSubjects->pluck('id')->toArray();
                $suplenteIds  = $teacher->suplenteSubjects->pluck('id')->toArray();

                // Available subjects: those with at least one free (or already-this-teacher) slot
                $assignableSubjects = $availableSubjects->filter(function ($s) use ($titularIds, $suplenteIds, $teacher) {
                    // Only include if there is at least one role this teacher can still take
                    $canTakeTitular   = is_null($s->titular_teacher_id) || $s->titular_teacher_id === $teacher->id;
                    $canTakeSuplente  = is_null($s->suplente_teacher_id) || $s->suplente_teacher_id === $teacher->id;
                    $alreadyTitular   = in_array($s->id, $titularIds);
                    $alreadySuplente  = in_array($s->id, $suplenteIds);
                    // Exclude if already occupying both roles (edge-case) or no slot left
                    if ($alreadyTitular && $alreadySuplente) return false;
                    return $canTakeTitular || $canTakeSuplente;
                });
            @endphp

            @if($assignableSubjects->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay clases disponibles para asignar (todas las clases ya tienen ambos profesores asignados).</p>
            @else
                <form action="{{ route('teachers.assignClass', $teacher) }}" method="POST" class="flex flex-wrap items-end gap-4">
                    @csrf

                    <div class="flex-1 min-w-[220px]">
                        <label for="subject_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Clase</label>
                        <select name="subject_id" id="subject_id" required
                                class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] text-sm">
                            <option value="">Seleccionar clase...</option>
                            @foreach($assignableSubjects as $s)
                                @php
                                    $label = ($s->subjectType->description ?? 'Sin tipo')
                                           . ' — ' . $s->day
                                           . ' ' . substr($s->start_time, 0, 5)
                                           . '–' . substr($s->end_time, 0, 5);
                                    $alreadyTitular  = in_array($s->id, $titularIds);
                                    $alreadySuplente = in_array($s->id, $suplenteIds);
                                    if ($alreadyTitular)   $label .= ' (ya titular)';
                                    if ($alreadySuplente)  $label .= ' (ya suplente)';
                                @endphp
                                <option value="{{ $s->id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-[160px]">
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rol</label>
                        <select name="role" id="role" required
                                class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] text-sm">
                            <option value="titular">Titular</option>
                            <option value="suplente">Suplente</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition text-sm">
                        <flux:icon name="plus" class="h-4 w-4" />
                        Asignar
                    </button>
                </form>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const form = document.getElementById('teacher-form');
        const saveBtn = document.getElementById('save-btn');
        const saveSpinner = document.getElementById('save-spinner');

        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;
            saveBtn.disabled = true;
            saveSpinner.classList.remove('hidden');
        });

        @if ($errors->any())
            (function () {
                const firstErrorEl = document.querySelector('.ring-2.ring-red-400, [aria-invalid="true"]');
                if (firstErrorEl) {
                    firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorEl.focus();
                }
            })();
        @endif
    })();
    </script>
    @endpush
</x-layouts.app>