<x-layouts.app title="Anotar alumno en clase">
    <div class="max-w-3xl mx-auto py-8 px-4">
         <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Anotar a {{ $student->name }}</h1>
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
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                        Seleccioná la clase (primer nivel), luego elegí el día y por último el horario. Al hacer clic en un horario se abrirá el diálogo para confirmar la inscripción.
                    </p>
                </div>

                <!-- Resumen: SOLO total -->
                <div class="bg-gray-50 dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 rounded p-3 flex flex-col items-start">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Valor cuota</h3>
                    <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="summary-total-price">—</div>
                </div>
            </div>

            {{-- Accordion: subjectType -> day -> time slots --}}
            <div id="accordion-root" class="space-y-3">
                @php
                    $groupedByType = $subjects->groupBy(function($s){ return $s->subject_type_id ?: 0; });
                    $preferredDayOrderNormalized = ['lunes','martes','miercoles','jueves','viernes','sabado'];
                    $normalize = function($str) {
                        if ($str === null) return '';
                        return strtolower(trim(\Illuminate\Support\Str::ascii($str)));
                    };
                @endphp

                @foreach($groupedByType as $typeId => $subs)
                    @php
                        $subjectType = $subs->first()->subjectType;
                        $typeLabel = $subjectType ? ($subjectType->description ?? $subjectType->value ?? $subjectType->name) : 'Sin materia';
                        $outerId = 'type-' . ($typeId ?: 'none');
                        $byDayRaw = $subs->groupBy('day');
                        $byDay = [];
                        foreach ($byDayRaw as $dayLabel => $collection) {
                            $norm = $normalize($dayLabel);
                            $byDay[$norm] = ['label' => $dayLabel, 'items' => $collection];
                        }
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
                            {{-- Días en orden preferido --}}
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
                                                        $hasTeacher = $sub->subjectType->has_teacher ?? true;
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
                                                        data-has-teacher="{{ $hasTeacher ? '1' : '0' }}"
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

                            {{-- Otros días --}}
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
                                                    $hasTeacher = $sub->subjectType->has_teacher ?? true;
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
                                                    data-has-teacher="{{ $hasTeacher ? '1' : '0' }}"
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

    <!-- Modal: lista de alumnos (sin cambios) -->
    <div id="students-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="max-w-2xl w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700">
                <h3 id="students-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Inscriptos</h3>
                <button id="students-modal-close" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 p-1">✕</button>
            </div>
            <div class="p-4">
                <div id="students-modal-body" class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                    <!-- listado inyectado por JS -->
                </div>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button id="students-modal-close-2" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded">Cerrar</button>
            </div>
        </div>
    </div>

    @push('scripts')
   
    <script>
    (function () {
        const csrfToken = '{{ csrf_token() }}';
        const enrollUrl = '{{ route("students.enroll", $student) }}';

        // Prices from server (defaults for has_teacher true/false)
        const subjectPrices = @json($subjectPricesForJs); // { teacher: {1:33000,...}, no_teacher: {1:26000,...} }
        // Initial price summary computed server-side
        let priceSummary = @json($priceSummary);

        function formatMoney(v) {
            if (v === null || v === undefined) return '—';
            return Number(v).toLocaleString('es-AR');
        }

        // Render only total
        function renderPriceSummary(summary) {
            const el = document.getElementById('summary-total-price');
            if (!el) return;
            el.textContent = (summary && summary.total) ? formatMoney(summary.total) : '—';
        }

        // Preview price following backend rule:
        // if newTeacherCount > 0 => appliedCount = min(newTeacherCount + newNoTeacherCount, 5) and use teacher prices
        // else => appliedCount = min(newNoTeacherCount, 5) and use no_teacher prices
        function previewPriceAdding(hasTeacher) {
            const teacherCount = (priceSummary.teacher_count || 0) + (hasTeacher ? 1 : 0);
            const noTeacherCount = (priceSummary.no_teacher_count || 0) + (hasTeacher ? 0 : 1);

            let appliedCount, appliedPrice;
            if (teacherCount > 0) {
                appliedCount = Math.min(teacherCount + noTeacherCount, 5);
                appliedPrice = (subjectPrices.teacher && subjectPrices.teacher[appliedCount] !== undefined) ? Number(subjectPrices.teacher[appliedCount]) : null;
            } else {
                appliedCount = Math.min(noTeacherCount, 5);
                appliedPrice = (subjectPrices.no_teacher && subjectPrices.no_teacher[appliedCount] !== undefined) ? Number(subjectPrices.no_teacher[appliedCount]) : null;
            }

            return {
                total: appliedPrice,
                appliedCount,
            };
        }

        // Accordion utility
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

        // POST enroll using fetch + formdata (returns JSON when called via AJAX)
        async function postEnroll(subjectId) {
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('subject_id', subjectId);

            const res = await fetch(enrollUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) {
                const text = await res.text();
                throw new Error(text || 'Network response not ok');
            }
            return await res.json();
        }

        // Update slot UI after enroll (unchanged)
        function markEnrolled(subjectId) {
            const btns = document.querySelectorAll(`[data-subject-id="${subjectId}"]`);
            btns.forEach(btn => {
                btn.setAttribute('data-is-already', '1');
                btn.disabled = true;

                const capacityAttr = btn.getAttribute('data-capacity') || '';
                const capacity = capacityAttr !== '' ? parseInt(capacityAttr, 10) : null;
                let enrolled = parseInt(btn.getAttribute('data-enrolled') || '0', 10);
                enrolled = isNaN(enrolled) ? 0 : enrolled + 1;
                btn.setAttribute('data-enrolled', String(enrolled));

                let free = '—';
                if (capacity !== null && !isNaN(capacity)) {
                    free = Math.max(0, capacity - enrolled);
                }

                const metaEl = btn.querySelector('.slot-meta');
                if (metaEl) {
                    metaEl.textContent = `Cupo: ${capacity !== null ? capacity : '—'} • Libre: ${free}`;
                }

                const actionEl = btn.querySelector('.slot-action-label');
                if (actionEl) actionEl.textContent = 'Inscripto';

                btn.classList.remove('bg-green-50','dark:bg-green-900/10','border-green-200','dark:border-green-800','text-green-900');
                btn.classList.remove('bg-red-50','dark:bg-red-900/20','border-red-300','dark:border-red-800','text-red-900');
                btn.classList.add('bg-yellow-50','dark:bg-yellow-900/20','border-yellow-300','dark:border-yellow-800','text-yellow-900');
            });
        }

        // Attach handlers to slot buttons
        function setupSlotButtons() {
            document.querySelectorAll('.js-slot-btn').forEach(btn => {
                if (btn._slotHandler) return;
                btn._slotHandler = true;

                // preview on mouseenter
                btn.addEventListener('mouseenter', (e) => {
                    if (btn.disabled) return;
                    const hasTeacher = btn.getAttribute('data-has-teacher') === '1';
                    const preview = previewPriceAdding(hasTeacher);
                    renderPriceSummary({ total: preview.total });
                });

                // restore current summary on mouseleave
                btn.addEventListener('mouseleave', (e) => {
                    renderPriceSummary(priceSummary);
                });

                btn.addEventListener('click', async (e) => {
                    if (btn.disabled) return;

                    const subjectId = btn.getAttribute('data-subject-id');
                    const start = btn.getAttribute('data-start');
                    const end = btn.getAttribute('data-end');
                    const day = btn.getAttribute('data-day');
                    const capacity = btn.getAttribute('data-capacity') || '—';
                    const enrolled = btn.getAttribute('data-enrolled') || 0;
                    const hasTeacher = btn.getAttribute('data-has-teacher') === '1';

                    const preview = previewPriceAdding(hasTeacher);

                    let html = `<div class="text-left">Día: <strong>${day}</strong><br>Horario: <strong>${start} - ${end}</strong><br>Cupo: <strong>${capacity}</strong><br>Inscriptos: <strong>${enrolled}</strong></div>`;
                    html += `<hr class="my-2">`;
                    html += `<div class="text-left text-sm">Total estimado si se anota: <strong>${preview.total ? formatMoney(preview.total) : '—'}</strong></div>`;

                    const result = await Swal.fire({
                        title: `Confirmar inscripción`,
                        html: html,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, anotar',
                        cancelButtonText: 'Cancelar'
                    });

                    if (result.isConfirmed) {
                        try {
                            const json = await postEnroll(subjectId);
                            if (json && json.success) {
                                markEnrolled(subjectId);

                                if (json.priceSummary) {
                                    priceSummary = json.priceSummary;
                                } else {
                                    // If server didn't return new summary, recalc locally conservatively:
                                    // increment teacher/noTeacher counts and then request total via preview logic
                                    priceSummary.teacher_count = (priceSummary.teacher_count || 0) + (hasTeacher ? 1 : 0);
                                    priceSummary.no_teacher_count = (priceSummary.no_teacher_count || 0) + (hasTeacher ? 0 : 1);
                                    // compute local total using previewPriceAdding logic:
                                    const p = previewPriceAdding(false); // this uses current priceSummary; but we already adjusted above
                                    priceSummary.total = p.total;
                                }

                                renderPriceSummary(priceSummary);

                                Swal.fire({ icon: 'success', title: 'Anotado', text: 'El alumno fue anotado correctamente.' });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: json && json.message ? json.message : 'No se pudo anotar.' });
                            }
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
            renderPriceSummary(priceSummary);
        });

        setTimeout(() => {
            setupAccordions(document);
            setupSlotButtons();
            renderPriceSummary(priceSummary);
        }, 50);
    })();
    </script>
    @endpush
</x-layouts.app>