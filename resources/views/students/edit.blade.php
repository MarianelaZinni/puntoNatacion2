<x-layouts.app title="Editar alumno">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar Alumno</h1>
        </div>

        {{-- Mensajes de session (success / error) --}}
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

        {{-- Lista general de errores --}}
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

        {{-- Student edit form (fields only). We close the form before rendering enroll/unenroll forms to avoid nested forms.
             The final "Guardar cambios" button is outside and will submit this form via JS. --}}
        <form action="{{ route('students.update', $student) }}" method="POST" id="student-form" class="space-y-8 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
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
                        value="{{ old('name', $student->name) }}"
                        placeholder="Ej. María Pérez"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('name') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                    >
                    @error('name')
                        <p id="name-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DNI / Documento --}}
                <div>
                    <label for="dni" class="block text-base font-medium text-gray-700 dark:text-gray-300">DNI / Documento <span class="text-red-500">*</span></label>
                    <input
                        id="dni"
                        name="dni"
                        type="text"
                        required
                        value="{{ old('dni', $student->dni) }}"
                        placeholder="Ej. 12345678"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('dni') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('dni') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('dni') ? 'dni-error' : '' }}"
                    >
                    @error('dni')
                        <p id="dni-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-base font-medium text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        value="{{ old('email', $student->email) }}"
                        placeholder="ejemplo@dominio.com"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('email') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                    >
                    @error('email')
                        <p id="email-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label for="phone" class="block text-base font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone', $student->phone) }}"
                        placeholder="+54 9 11 1234 5678"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('phone') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('phone') ? 'phone-error' : '' }}"
                    >
                    @error('phone')
                        <p id="phone-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Dirección --}}
            <div>
                <label for="address" class="block text-base font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                <input
                    id="address"
                    name="address"
                    type="text"
                    value="{{ old('address', $student->address) }}"
                    placeholder="Calle, número, ciudad"
                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('address') ring-2 ring-red-400 @enderror"
                    aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                    aria-describedby="{{ $errors->has('address') ? 'address-error' : '' }}"
                >
                @error('address')
                    <p id="address-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </form>
        {{-- end student update form --}}

        {{-- Classes management section (enroll / list enrolled with unenroll action) --}}
        <div class="mt-8 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Clases inscritas</h2>

            {{-- Table of enrolled classes with "Desinscribir" button --}}
            @if($student->subjects->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                    El alumno no está inscripto en ninguna clase.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase">
                                <th class="px-3 py-2">Materia</th>
                                <th class="px-3 py-2">Día</th>
                                <th class="px-3 py-2">Horario</th>
                                <th class="px-3 py-2">Cupo</th>
                                <th class="px-3 py-2">Inscriptos</th>
                                <th class="px-3 py-2">Libre</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->subjects as $subject)
                                @php
                                    $subjectType = $subject->subjectType;
                                    $title = $subjectType->description ?? $subjectType->value ?? 'Sin materia';
                                    $enrolled = $subject->students_count ?? ($subject->students ? $subject->students->count() : 0);
                                    $free = max(0, ($subject->capacity ?? 0) - $enrolled);
                                @endphp
                                <tr class="border-t">
                                    <td class="px-3 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $title }}</td>
                                    <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ $subject->day }}</td>
                                    <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ ($subject->start_time ?? '') . ' - ' . ($subject->end_time ?? '') }}</td>
                                    <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ $subject->capacity ?? '-' }}</td>
                                    <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ $enrolled }}</td>
                                    <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ $free }}</td>
                                    <td class="px-3 py-3">
                                        <form action="{{ route('students.unenroll', $student) }}" method="POST" onsubmit="return confirmUnenroll(event, this);">
                                            @csrf
                                            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                            <button type="submit" class="inline-flex items-center px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700">Desinscribir</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        {{-- end classes section --}}

        {{-- Actions (moved below classes). The Save button submits the student-form via JS. --}}
        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('students.index') }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                Cancelar
            </a>

            <button
                type="button"
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

    @push('scripts')
    <!-- SweetAlert2 (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    (function () {
        const form = document.getElementById('student-form');
        const saveBtn = document.getElementById('save-btn');
        const saveSpinner = document.getElementById('save-spinner');

        // Submit the student form when the external save button is clicked
        saveBtn.addEventListener('click', function (e) {
            // Basic HTML5 validity check before submitting
            if (!form.checkValidity()) {
                // Let browser show validation messages: trigger native reporting
                // by focusing the first invalid element
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.reportValidity();
                    firstInvalid.focus();
                }
                return;
            }
            // Disable button and show spinner immediately to avoid double submissions
            saveBtn.disabled = true;
            saveSpinner.classList.remove('hidden');
            form.submit();
        });

        // If there are server side errors, highlight the first invalid field and scroll to it
        @if ($errors->any())
            (function () {
                const firstErrorEl = document.querySelector('.ring-2.ring-red-400, [aria-invalid="true"]');
                if (firstErrorEl) {
                    firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorEl.focus();
                }
            })();
        @endif

        // Flash close handlers
        document.getElementById('flash-success-close')?.addEventListener('click', function(){ this.closest('[id^=flash-]')?.remove(); });
        document.getElementById('flash-error-close')?.addEventListener('click', function(){ this.closest('[id^=flash-]')?.remove(); });

        // Confirm unenroll with SweetAlert (used by the onsubmit handler of the unenroll forms)
        window.confirmUnenroll = function(e, formEl) {
            e.preventDefault();
            if (typeof Swal === 'undefined') {
                return confirm('¿Seguro que querés desinscribir al alumno de esta clase?');
            }

            Swal.fire({
                title: 'Desinscribir',
                text: '¿Seguro que querés desinscribir al alumno de esta clase?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, desinscribir',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    formEl.submit();
                }
            });

            return false;
        };
    })();
    </script>
    @endpush
</x-layouts.app>