<x-layouts.app title="Editar alumno">
    <div class="max-w-3xl mx-auto py-8 px-4">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
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

        {{-- Student edit form --}}
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
                    <label for="email" class="block text-base font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
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

            {{-- Fecha de Nacimiento --}}
<div>
    <label for="birth_date" class="block text-base font-medium text-gray-700 dark:text-gray-300">Fecha de Nacimiento</label>
    <input
        id="birth_date"
        name="birth_date"
        type="date"
        value="{{ old('birth_date', $student->birth_date ? $student->birth_date->format('Y-m-d') : '') }}"
        max="{{ date('Y-m-d') }}"
        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('birth_date') ring-2 ring-red-400 @enderror"
        aria-invalid="{{ $errors->has('birth_date') ? 'true' : 'false' }}"
        aria-describedby="{{ $errors->has('birth_date') ? 'birth_date-error' : '' }}"
        onchange="calculateAge()"
    >
    @error('birth_date')
        <p id="birth_date-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

{{-- Edad (calculada automáticamente, solo lectura) --}}
<div>
    <label for="age" class="block text-base font-medium text-gray-700 dark:text-gray-300">Edad</label>
    <input
        id="age"
        name="age"
        type="text"
        readonly
        value="{{ $student->age !== null ? $student->age . ($student->age === 1 ? ' año' : ' años') : '' }}"
        placeholder="Se calcula automáticamente"
        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-900 text-gray-900 dark:text-gray-100 shadow-sm text-base leading-relaxed cursor-not-allowed"
        tabindex="-1"
    >
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">La edad se calcula automáticamente según la fecha de nacimiento</p>
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

             {{-- Observaciones --}}
            <div class="sm:col-span-2">
                <label for="observations" class="block text-base font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                <textarea
                    id="observations"
                    name="observations"
                    rows="3"
                    placeholder="Notas adicionales sobre el alumno..."
                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('observations') ring-2 ring-red-400 @enderror"
                    aria-invalid="{{ $errors->has('observations') ? 'true' : 'false' }}"
                    aria-describedby="{{ $errors->has('observations') ? 'observations-error' : '' }}"
                >{{ old('observations', $student->observations) }}</textarea>
                @error('observations')
                    <p id="observations-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

        </form>
        {{-- end student update form --}}

        {{-- Classes management section (enroll / list enrolled with unenroll action) --}}
        <div class="mt-8 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Clases inscritas</h2>

                {{-- Cuota mensual (solo total) --}}
                <div class="bg-gray-50 dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 rounded p-3 text-right">
                    <div class="text-sm font-bold text-gray-700 dark:text-gray-200">Cuota mensual</div>
                    <div id="summary-total-price" class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1">
                        @php $total = $priceSummary['total'] ?? null; @endphp
                        {{ $total !== null ? number_format($total, 0, ',', '.') : '—' }}
                    </div>

                    @if(!empty($priceSummary['details']))
                        <div id="summary-details" class="mt-2 text-xs text-red-600 dark:text-red-300">
                            @foreach($priceSummary['details'] as $d)
                                <div>{{ $d }}</div>
                            @endforeach
                        </div>
                    @else
                        <div id="summary-details" class="mt-2 text-xs text-red-600 dark:text-red-300" style="display:none;"></div>
                    @endif
                </div>
            </div>

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
                                    $hasTeacher = $subjectType->has_teacher ?? true;
                                @endphp
                                <tr class="border-t" data-subject-id="{{ $subject->id }}" data-has-teacher="{{ $hasTeacher ? '1' : '0' }}">
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

        {{-- Actions --}}
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
        const csrfToken = '{{ csrf_token() }}';

        // Initial price summary passed from server
        let priceSummary = @json($priceSummary ?? []);

        // Prices defaults mapping (optional, used only for local preview fallback)
        const subjectPrices = @json($subjectPricesForJs ?? ['teacher'=>[], 'no_teacher'=>[]]);

        // Helper to format money
        function formatMoney(v) {
            if (v === null || v === undefined) return '—';
            return Number(v).toLocaleString('es-AR');
        }

        function renderPriceSummary(summary) {
            const el = document.getElementById('summary-total-price');
            const detailsEl = document.getElementById('summary-details');
            if (!el) return;
            el.textContent = (summary && summary.total) ? formatMoney(summary.total) : '—';
            if (detailsEl) {
                if (summary && summary.details && summary.details.length) {
                    detailsEl.style.display = 'block';
                    detailsEl.innerHTML = summary.details.map(d => `<div>${d}</div>`).join('');
                } else {
                    detailsEl.style.display = 'none';
                    detailsEl.innerHTML = '';
                }
            }
        }

        // Submit the student form when the external save button is clicked
        saveBtn.addEventListener('click', function (e) {
            if (!form.checkValidity()) {
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.reportValidity();
                    firstInvalid.focus();
                }
                return;
            }
            saveBtn.disabled = true;
            saveSpinner.classList.remove('hidden');
            form.submit();
        });

        // Flash close handlers
        document.getElementById('flash-success-close')?.addEventListener('click', function(){ this.closest('[id^=flash-]')?.remove(); });
        document.getElementById('flash-error-close')?.addEventListener('click', function(){ this.closest('[id^=flash-]')?.remove(); });

        // Confirm unenroll and use AJAX to perform unenroll and refresh quota
        window.confirmUnenroll = function(e, formEl) {
            e.preventDefault();
            if (typeof Swal === 'undefined') {
                // fallback to native confirm + submit (full page)
                if (confirm('¿Seguro que querés desinscribir al alumno de esta clase?')) {
                    formEl.submit();
                }
                return false;
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
            }).then(async (result) => {
                if (!result.isConfirmed) return;
                try {
                    // Build form data (includes CSRF token hidden input already)
                    const formData = new FormData(formEl);
                    // Send as AJAX
                    const res = await fetch(formEl.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    });

                    if (!res.ok) {
                        const text = await res.text();
                        throw new Error(text || 'Error en la petición');
                    }

                    const json = await res.json();

                    if (json && json.success) {
                        // Remove the table row for that subject
                        const subjectId = json.subject_id || formEl.querySelector('input[name="subject_id"]').value;
                        const row = document.querySelector(`tr[data-subject-id="${subjectId}"]`);
                        if (row) row.remove();

                        // Update local priceSummary with server value if provided
                        if (json.priceSummary) {
                            priceSummary = json.priceSummary;
                        } else {
                            // fallback: try decrement locally (best-effort)
                            // If the detached subject had data-has-teacher, decrement accordingly.
                            const detachedRow = document.querySelector(`tr[data-subject-id="${subjectId}"]`);
                            if (detachedRow) {
                                const wasTeacher = detachedRow.dataset.hasTeacher === '1';
                                if (wasTeacher) {
                                    priceSummary.teacher_count = Math.max(0, (priceSummary.teacher_count || 0) - 1);
                                } else {
                                    priceSummary.no_teacher_count = Math.max(0, (priceSummary.no_teacher_count || 0) - 1);
                                }
                                // recompute total using server price mapping (best-effort)
                                if ((priceSummary.teacher_count || 0) + (priceSummary.no_teacher_count || 0) > 0) {
                                    let appliedCount, appliedPrice;
                                    if ((priceSummary.teacher_count || 0) > 0) {
                                        appliedCount = Math.min((priceSummary.teacher_count || 0) + (priceSummary.no_teacher_count || 0), 5);
                                        appliedPrice = (subjectPrices.teacher && subjectPrices.teacher[appliedCount] !== undefined) ? Number(subjectPrices.teacher[appliedCount]) : null;
                                    } else {
                                        appliedCount = Math.min(priceSummary.no_teacher_count || 0, 5);
                                        appliedPrice = (subjectPrices.no_teacher && subjectPrices.no_teacher[appliedCount] !== undefined) ? Number(subjectPrices.no_teacher[appliedCount]) : null;
                                    }
                                    priceSummary.total = appliedPrice;
                                } else {
                                    priceSummary.total = 0;
                                }
                            }
                        }

                        renderPriceSummary(priceSummary);

                        Swal.fire({ icon: 'success', title: 'Desinscripto', text: json.message || 'Alumno desinscripto correctamente.' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: json && json.message ? json.message : 'No se pudo desinscribir.' });
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al desinscribir. Revisa la consola.' });
                }
            });

            return false;
        };

        // On load render initial summary
        document.addEventListener('DOMContentLoaded', function () {
            renderPriceSummary(priceSummary);
        });

        // If server-side validation errors exist, focus first invalid field
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
    <script>
(function() {
    // Función para calcular la edad
    window.calculateAge = function() {
        const birthDateInput = document.getElementById('birth_date');
        const ageInput = document.getElementById('age');
        
        if (birthDateInput && birthDateInput.value) {
            const birthDate = new Date(birthDateInput.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            // Ajustar si aún no ha cumplido años este año
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age >= 0) {
                ageInput.value = age + (age === 1 ? ' año' : ' años');
            } else {
                ageInput.value = '';
            }
        } else {
            ageInput.value = '';
        }
    };

    // Calcular edad al cargar la página si hay valor previo
    if (document.getElementById('birth_date').value) {
        calculateAge();
    }
})();
</script>
    @endpush
</x-layouts.app>