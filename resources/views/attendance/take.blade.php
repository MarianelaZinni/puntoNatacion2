<x-layouts.app title="Tomar Lista">
    <div class="max-w-3xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Lista de asistencia</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $subject->subjectType->description ?? 'Sin tipo' }}
                    &mdash; {{ $subject->day }}
                    {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
                    &mdash; <span class="font-medium">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                </p>
            </div>
            <a href="{{ route('attendance.index') }}"
               class="inline-flex items-center px-4 py-2 rounded text-sm text-white bg-gray-500 hover:bg-gray-600 transition">
                ← Volver
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
                        // If there's an existing record, use it; otherwise default to present (true)
                        $isPresent = array_key_exists($student->id, $existing)
                            ? (bool) $existing[$student->id]
                            : true;
                    @endphp
                    <li class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-zinc-800 transition attendance-row"
                        data-present="{{ $isPresent ? '1' : '0' }}">

                        <div class="flex items-center gap-4">
                            {{-- Presence toggle checkbox --}}
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="present_students[]"
                                    value="{{ $student->id }}"
                                    class="sr-only peer attendance-checkbox"
                                    {{ $isPresent ? 'checked' : '' }}
                                >
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#29b1dc] rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-500"></div>
                            </label>

                            {{-- Student info --}}
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</p>
                                @if($student->dni)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $student->dni }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Status badge --}}
                        <span class="attendance-badge text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $isPresent
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'
                                : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                            {{ $isPresent ? 'Presente' : 'Ausente' }}
                        </span>
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
        // --- Toggle badge color when checkbox changes ---
        function updateRow(checkbox) {
            const li     = checkbox.closest('li.attendance-row');
            const badge  = li.querySelector('.attendance-badge');
            const present = checkbox.checked;

            if (present) {
                badge.textContent = 'Presente';
                badge.className = 'attendance-badge text-xs font-semibold px-2.5 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
            } else {
                badge.textContent = 'Ausente';
                badge.className = 'attendance-badge text-xs font-semibold px-2.5 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
            }
        }

        function updateSummary() {
            const boxes   = document.querySelectorAll('.attendance-checkbox');
            let present   = 0;
            boxes.forEach(cb => { if (cb.checked) present++; });
            document.getElementById('count-present').textContent = present;
            document.getElementById('count-absent').textContent  = boxes.length - present;
        }

        // Attach listeners
        document.querySelectorAll('.attendance-checkbox').forEach(cb => {
            cb.addEventListener('change', function () {
                updateRow(this);
                updateSummary();
            });
        });

        // Bulk buttons
        document.getElementById('mark-all-present')?.addEventListener('click', function () {
            document.querySelectorAll('.attendance-checkbox').forEach(cb => {
                cb.checked = true;
                updateRow(cb);
            });
            updateSummary();
        });

        document.getElementById('mark-all-absent')?.addEventListener('click', function () {
            document.querySelectorAll('.attendance-checkbox').forEach(cb => {
                cb.checked = false;
                updateRow(cb);
            });
            updateSummary();
        });

        // Form spinner on save
        const form = document.getElementById('attendance-form');
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
