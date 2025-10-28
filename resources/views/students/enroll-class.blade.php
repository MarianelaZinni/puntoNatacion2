<x-layouts.app title="Anotar alumno en clase">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Anotar a {{ $student->name }}</h1>
            <!-- Volver a la lista de alumnos, con el estilo pedido -->
            <a href="{{ route('students.index') }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                Volver
            </a>
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

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                Seleccioná la clase (primer nivel), luego elegí el día y por último el horario. Al hacer clic en un horario se abrirá el diálogo para confirmar la inscripción.
            </p>

            {{-- Accordion: subjectType -> day -> time slots --}}
            <div id="accordion-root" class="space-y-3">
                @php
                    // Agrupar por tipo de materia (subject_type_id)
                    $groupedByType = $subjects->groupBy(function($s){ return $s->subject_type_id ?: 0; });

                    // Orden preferido (normalizado) para los días en el segundo nivel
                    // He incluido "viernes" en el orden para que aparezca antes de sábado.
                    $preferredDayOrderNormalized = ['lunes','martes','miercoles','jueves','viernes','sabado'];

                    // Helper para normalizar nombres (quitar tildes y pasar a minúsculas)
                    $normalize = function($str) {
                        if ($str === null) return '';
                        // \Illuminate\Support\Str::ascii convierte acentos a ASCII, luego pasamos a minúsculas y limpiamos espacios
                        return strtolower(trim(\Illuminate\Support\Str::ascii($str)));
                    };
                @endphp

                @foreach($groupedByType as $typeId => $subs)
                    @php
                        $subjectType = $subs->first()->subjectType;
                        $typeLabel = $subjectType ? ($subjectType->description ?? $subjectType->value ?? $subjectType->name) : 'Sin materia';
                        $outerId = 'type-' . ($typeId ?: 'none');

                        // Agrupar por día pero creando un map normalizado => ['label' => originalLabel, 'items' => collection]
                        $byDayRaw = $subs->groupBy('day');
                        $byDay = [];
                        foreach ($byDayRaw as $dayLabel => $collection) {
                            $norm = $normalize($dayLabel);
                            $byDay[$norm] = ['label' => $dayLabel, 'items' => $collection];
                        }

                        // días presentes (normalizados)
                        $presentDaysNormalized = array_keys($byDay);
                        $handledDays = [];
                    @endphp

                    <div class="border rounded-lg overflow-hidden">
                        <button type="button"
                                class="w-full text-left px-4 py-3 flex items-center justify-between bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700"
                                data-accordion-toggle="{{ $outerId }}">
                            <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $typeLabel }}</span>
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-300 transform transition-transform" data-accordion-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="{{ $outerId }}" class="px-4 py-3 hidden bg-white dark:bg-zinc-900">
                            {{-- Primero renderizamos los días según el orden preferido (solo si existen) --}}
                            @foreach($preferredDayOrderNormalized as $dayNorm)
                                @if(isset($byDay[$dayNorm]) && !empty($byDay[$dayNorm]['items']))
                                    @php
                                        $dayLabel = $byDay[$dayNorm]['label'];
                                        $daySubs = $byDay[$dayNorm]['items'];
                                        $innerId = $outerId . '-day-' . \Illuminate\Support\Str::slug($dayLabel);
                                        $handledDays[] = $dayNorm;
                                    @endphp

                                    <div class="mb-4 border rounded">
                                        <button type="button"
                                                class="w-full text-left px-3 py-2 bg-gray-50 dark:bg-zinc-900/40 hover:bg-gray-100 dark:hover:bg-zinc-800 flex items-center justify-between"
                                                data-accordion-toggle="{{ $innerId }}">
                                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $dayLabel }}</span>
                                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-300 transform transition-transform" data-accordion-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div id="{{ $innerId }}" class="px-3 py-3 hidden bg-white dark:bg-zinc-900">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach($daySubs as $sub)
                                                    @php
                                                        $enrolled = $sub->students_count ?? ($sub->students ? $sub->students->count() : 0);
                                                        $free = max(0, ($sub->capacity ?? 0) - $enrolled);
                                                        $isFull = ($sub->capacity !== null) && ($enrolled >= $sub->capacity);
                                                        $isAlready = $student->subjects->contains('id', $sub->id);
                                                        $btnClasses = $isAlready
                                                            ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-800 text-yellow-900'
                                                            : ($isFull
                                                                ? 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-800 text-red-900'
                                                                : 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800 text-green-900');
                                                    @endphp

                                                    <button
                                                        class="js-slot-btn flex items-center justify-between gap-3 px-3 py-2 rounded border text-sm text-left {{ $btnClasses }}"
                                                        data-subject-id="{{ $sub->id }}"
                                                        data-start="{{ $sub->start_time }}"
                                                        data-end="{{ $sub->end_time }}"
                                                        data-day="{{ $sub->day }}"
                                                        data-capacity="{{ $sub->capacity ?? '' }}"
                                                        data-enrolled="{{ $enrolled }}"
                                                        data-is-full="{{ $isFull ? '1' : '0' }}"
                                                        data-is-already="{{ $isAlready ? '1' : '0' }}"
                                                        @if($isFull || $isAlready) disabled @endif
                                                    >
                                                        <div>
                                                            <div class="font-medium text-gray-800 dark:text-gray-100 slot-time-label">
                                                                {{ $sub->start_time }} - {{ $sub->end_time }}
                                                            </div>
                                                            <div class="text-xs text-gray-600 dark:text-gray-300 slot-meta">
                                                                Cupo: {{ $sub->capacity ?? '—' }} • Libre: {{ $free }}
                                                            </div>
                                                        </div>

                                                        <div class="text-xs text-gray-600 dark:text-gray-300 slot-action-label">
                                                            @if($isAlready)
                                                                Inscripto
                                                            @elseif($isFull)
                                                                Completo
                                                            @else
                                                                Anotar
                                                            @endif
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            {{-- Luego renderizamos los demás días que no están en la lista preferida, manteniendo su orden original --}}
                            @php
                                $otherDays = collect($presentDaysNormalized)->reject(function($d) use ($handledDays) {
                                    return in_array($d, $handledDays);
                                })->values()->all();
                            @endphp

                            @foreach($otherDays as $dayNorm)
                                @php
                                    $dayLabel = $byDay[$dayNorm]['label'];
                                    $daySubs = $byDay[$dayNorm]['items'];
                                    $innerId = $outerId . '-day-' . \Illuminate\Support\Str::slug($dayLabel);
                                @endphp

                                <div class="mb-4 border rounded">
                                    <button type="button"
                                            class="w-full text-left px-3 py-2 bg-gray-50 dark:bg-zinc-900/40 hover:bg-gray-100 dark:hover:bg-zinc-800 flex items-center justify-between"
                                            data-accordion-toggle="{{ $innerId }}">
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $dayLabel }}</span>
                                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-300 transform transition-transform" data-accordion-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div id="{{ $innerId }}" class="px-3 py-3 hidden bg-white dark:bg-zinc-900">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($daySubs as $sub)
                                                @php
                                                    $enrolled = $sub->students_count ?? ($sub->students ? $sub->students->count() : 0);
                                                    $free = max(0, ($sub->capacity ?? 0) - $enrolled);
                                                    $isFull = ($sub->capacity !== null) && ($enrolled >= $sub->capacity);
                                                    $isAlready = $student->subjects->contains('id', $sub->id);
                                                    $btnClasses = $isAlready
                                                        ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-800 text-yellow-900'
                                                        : ($isFull
                                                            ? 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-800 text-red-900'
                                                            : 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800 text-green-900');
                                                @endphp

                                                <button
                                                    class="js-slot-btn flex items-center justify-between gap-3 px-3 py-2 rounded border text-sm text-left {{ $btnClasses }}"
                                                    data-subject-id="{{ $sub->id }}"
                                                    data-start="{{ $sub->start_time }}"
                                                    data-end="{{ $sub->end_time }}"
                                                    data-day="{{ $sub->day }}"
                                                    data-capacity="{{ $sub->capacity ?? '' }}"
                                                    data-enrolled="{{ $enrolled }}"
                                                    data-is-full="{{ $isFull ? '1' : '0' }}"
                                                    data-is-already="{{ $isAlready ? '1' : '0' }}"
                                                    @if($isFull || $isAlready) disabled @endif
                                                >
                                                    <div>
                                                        <div class="font-medium text-gray-800 dark:text-gray-100 slot-time-label">
                                                            {{ $sub->start_time }} - {{ $sub->end_time }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 dark:text-gray-300 slot-meta">
                                                            Cupo: {{ $sub->capacity ?? '—' }} • Libre: {{ $free }}
                                                        </div>
                                                    </div>

                                                    <div class="text-xs text-gray-600 dark:text-gray-300 slot-action-label">
                                                        @if($isAlready)
                                                            Inscripto
                                                        @elseif($isFull)
                                                            Completo
                                                        @else
                                                            Anotar
                                                        @endif
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                @endforeach
            </div>
            {{-- end accordion --}}
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    (function () {
        const csrfToken = '{{ csrf_token() }}';
        const enrollUrl = '{{ route("students.enroll", $student) }}';

        // Accordion utility (works for nested accordions)
        function setupAccordions(rootSelector = document) {
            rootSelector.querySelectorAll('[data-accordion-toggle]').forEach(btn => {
                if (btn._accordionAttached) return;
                btn._accordionAttached = true;
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-accordion-toggle');
                    const panel = document.getElementById(id);
                    if (!panel) return;
                    const icon = btn.querySelector('[data-accordion-icon]');
                    const opened = !panel.classList.contains('hidden');
                    if (opened) {
                        panel.classList.add('hidden');
                        if (icon) icon.classList.remove('rotate-180');
                    } else {
                        panel.classList.remove('hidden');
                        if (icon) icon.classList.add('rotate-180');
                    }
                });
            });
        }

        // POST enroll using fetch + formdata
        async function postEnroll(subjectId) {
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('subject_id', subjectId);

            const res = await fetch(enrollUrl, { method: 'POST', body: formData });
            if (!res.ok) {
                const text = await res.text();
                throw new Error(text || 'Network response not ok');
            }
            // return parsed json if any, else null
            try { return await res.json(); } catch (e) { return null; }
        }

        // Update buttons and counters in-place after successful enrollment (no page reload)
        function markEnrolled(subjectId) {
            const btns = document.querySelectorAll(`[data-subject-id="${subjectId}"]`);
            btns.forEach(btn => {
                // set state attributes
                btn.setAttribute('data-is-already', '1');
                btn.disabled = true;

                // update enrolled count and free seats
                const capacityAttr = btn.getAttribute('data-capacity') || '';
                const capacity = capacityAttr !== '' ? parseInt(capacityAttr, 10) : null;
                let enrolled = parseInt(btn.getAttribute('data-enrolled') || '0', 10);
                enrolled = isNaN(enrolled) ? 0 : enrolled + 1;
                btn.setAttribute('data-enrolled', String(enrolled));

                let free = '—';
                if (capacity !== null && !isNaN(capacity)) {
                    free = Math.max(0, capacity - enrolled);
                }

                // update meta text (Cupo / Libre)
                const metaEl = btn.querySelector('.slot-meta');
                if (metaEl) {
                    metaEl.textContent = `Cupo: ${capacity !== null ? capacity : '—'} • Libre: ${free}`;
                }

                // update right-side action label
                const actionEl = btn.querySelector('.slot-action-label');
                if (actionEl) actionEl.textContent = 'Inscripto';

                // swap classes to "inscripto" style (yellow)
                btn.classList.remove('bg-green-50','dark:bg-green-900/10','border-green-200','dark:border-green-800','text-green-900');
                btn.classList.remove('bg-red-50','dark:bg-red-900/20','border-red-300','dark:border-red-800','text-red-900');
                // add yellow classes
                btn.classList.add('bg-yellow-50','dark:bg-yellow-900/20','border-yellow-300','dark:border-yellow-800','text-yellow-900');
            });
        }

        // Attach handlers to slot buttons
        function setupSlotButtons() {
            document.querySelectorAll('.js-slot-btn').forEach(btn => {
                if (btn._slotHandler) return;
                btn._slotHandler = true;
                btn.addEventListener('click', async (e) => {
                    // ignore disabled buttons
                    if (btn.disabled) return;

                    const subjectId = btn.getAttribute('data-subject-id');
                    const start = btn.getAttribute('data-start');
                    const end = btn.getAttribute('data-end');
                    const day = btn.getAttribute('data-day');
                    const capacity = btn.getAttribute('data-capacity') || '—';
                    const enrolled = btn.getAttribute('data-enrolled') || 0;

                    const result = await Swal.fire({
                        title: `Confirmar inscripción`,
                        html: `<div class="text-left">Día: <strong>${day}</strong><br>Horario: <strong>${start} - ${end}</strong><br>Cupo: <strong>${capacity}</strong><br>Inscriptos: <strong>${enrolled}</strong></div>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, anotar',
                        cancelButtonText: 'Cancelar'
                    });

                    if (result.isConfirmed) {
                        try {
                            await postEnroll(subjectId);
                            // update UI in-place
                            markEnrolled(subjectId);
                            Swal.fire({ icon: 'success', title: 'Anotado', text: 'El alumno fue anotado correctamente.' });
                        } catch (err) {
                            console.error(err);
                            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo anotar. Revisa la consola.' });
                        }
                    }
                });
            });
        }

        // Initial setup
        document.addEventListener('DOMContentLoaded', function () {
            setupAccordions(document);
            setupSlotButtons();
        });

        // If the view is injected client-side, initialize shortly after
        setTimeout(() => {
            setupAccordions(document);
            setupSlotButtons();
        }, 50);
    })();
    </script>
    @endpush
</x-layouts.app>