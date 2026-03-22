<x-layouts.app title="Tomar Lista">
    <div class="max-w-3xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Lista de asistencia</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $subject->subjectType->description ?? 'Sin tipo' }}
                    &mdash; {{ $subject->day }}
                    {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
                    &mdash; <span class="font-medium">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center px-4 py-2 rounded text-sm text-white bg-gray-400 hover:bg-gray-500 transition">
                    ⌂ Tablero
                </a>
                <a href="{{ route('attendance.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded text-sm text-white bg-gray-500 hover:bg-gray-600 transition">
                    ← Asistencia
                </a>
            </div>
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
            <div class="mb-4 p-3 rounded bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 text-sm">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if($subject->students->isEmpty())
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm text-sm text-gray-500 dark:text-gray-400">
                No hay alumnos inscriptos en esta clase.
            </div>
        @else
        <form action="{{ route('attendance.store') }}" method="POST" id="attendance-form">
            @csrf
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">

                {{-- Bulk-toggle header --}}
                <div class="flex items-center justify-between px-6 py-3 bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        {{ $subject->students->count() }} alumno(s) inscripto(s)
                    </span>
                    <div class="flex gap-3">
                        <button type="button" id="mark-all-present"
                                class="text-xs px-3 py-1 rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50 transition">
                            Marcar todos presentes
                        </button>
                        <button type="button" id="mark-all-absent"
                                class="text-xs px-3 py-1 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50 transition">
                            Marcar todos ausentes
                        </button>
                    </div>
                </div>

                {{-- Student rows --}}
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($subject->students as $student)
                    @php
                        // If there's an existing record, use it; otherwise default to absent (false)
                        $isPresent = array_key_exists($student->id, $existing)
                            ? (bool) $existing[$student->id]
                            : false;
                    @endphp
                    <li class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-zinc-800 transition attendance-row">

                        {{-- Hidden checkbox used for form submission --}}
                        <input
                            type="checkbox"
                            name="present_students[]"
                            value="{{ $student->id }}"
                            class="sr-only attendance-checkbox"
                            {{ $isPresent ? 'checked' : '' }}
                        >

                        {{-- Student info --}}
                        <div class="flex items-center gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</p>
                                @if($student->dni)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $student->dni }}</p>
                                @endif
                            </div>
                            <a href="{{ route('students.show', $student) }}"
                               title="Ver ficha de {{ $student->name }}"
                               class="inline-flex items-center justify-center h-7 w-7 rounded-full text-[#29b1dc] hover:bg-[#29b1dc]/10 transition"
                               target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>

                        {{-- Per-row Presente / Ausente segmented buttons --}}
                        <div class="inline-flex rounded-lg overflow-hidden border border-gray-200 dark:border-zinc-700 text-sm font-medium">
                            <button type="button"
                                    data-action="present"
                                    class="row-btn-present px-4 py-1.5 transition
                                        {{ $isPresent
                                            ? 'bg-green-500 text-white'
                                            : 'bg-white dark:bg-zinc-900 text-gray-500 dark:text-gray-400 hover:bg-green-50 dark:hover:bg-green-900/20' }}">
                                ✓ Presente
                            </button>
                            <button type="button"
                                    data-action="absent"
                                    class="row-btn-absent border-l border-gray-200 dark:border-zinc-700 px-4 py-1.5 transition
                                        {{ !$isPresent
                                            ? 'bg-red-500 text-white'
                                            : 'bg-white dark:bg-zinc-900 text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20' }}">
                                ✗ Ausente
                            </button>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Summary bar + Save button --}}
            <div class="mt-6 flex items-center justify-between gap-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Presentes: <span id="count-present" class="font-semibold text-green-700 dark:text-green-400">0</span>
                    &nbsp;/&nbsp;
                    Ausentes: <span id="count-absent" class="font-semibold text-red-600 dark:text-red-400">0</span>
                </p>

                <button
                    type="submit"
                    id="save-btn"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                    <svg id="save-spinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    {{ $alreadySaved ? 'Actualizar lista' : 'Guardar lista' }}
                </button>
            </div>
        </form>
        @endif
    </div>

    @push('scripts')
    <script>
    (function () {
        /**
         * Mark a row as present (true) or absent (false).
         * Updates the hidden checkbox, button highlight states, and the summary.
         */
        function setRow(li, present) {
            const checkbox  = li.querySelector('.attendance-checkbox');
            const btnPresent = li.querySelector('.row-btn-present');
            const btnAbsent  = li.querySelector('.row-btn-absent');

            checkbox.checked = present;

            if (present) {
                btnPresent.className = 'row-btn-present px-4 py-1.5 transition bg-green-500 text-white';
                btnAbsent.className  = 'row-btn-absent border-l border-gray-200 dark:border-zinc-700 px-4 py-1.5 transition bg-white dark:bg-zinc-900 text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20';
            } else {
                btnPresent.className = 'row-btn-present px-4 py-1.5 transition bg-white dark:bg-zinc-900 text-gray-500 dark:text-gray-400 hover:bg-green-50 dark:hover:bg-green-900/20';
                btnAbsent.className  = 'row-btn-absent border-l border-gray-200 dark:border-zinc-700 px-4 py-1.5 transition bg-red-500 text-white';
            }
        }

        function updateSummary() {
            const boxes = document.querySelectorAll('.attendance-checkbox');
            let present = 0;
            boxes.forEach(cb => { if (cb.checked) present++; });
            document.getElementById('count-present').textContent = present;
            document.getElementById('count-absent').textContent  = boxes.length - present;
        }

        // Per-row buttons
        document.querySelectorAll('.attendance-row').forEach(li => {
            li.querySelector('.row-btn-present')?.addEventListener('click', function () {
                setRow(li, true);
                updateSummary();
            });
            li.querySelector('.row-btn-absent')?.addEventListener('click', function () {
                setRow(li, false);
                updateSummary();
            });
        });

        // Bulk buttons
        document.getElementById('mark-all-present')?.addEventListener('click', function () {
            document.querySelectorAll('.attendance-row').forEach(li => setRow(li, true));
            updateSummary();
        });
        document.getElementById('mark-all-absent')?.addEventListener('click', function () {
            document.querySelectorAll('.attendance-row').forEach(li => setRow(li, false));
            updateSummary();
        });

        // Form spinner on save
        const form    = document.getElementById('attendance-form');
        const saveBtn = document.getElementById('save-btn');
        const spinner = document.getElementById('save-spinner');
        form?.addEventListener('submit', function () {
            saveBtn.disabled = true;
            spinner?.classList.remove('hidden');
        });

        // Initial summary count
        updateSummary();
    })();
    </script>
    @endpush
</x-layouts.app>
