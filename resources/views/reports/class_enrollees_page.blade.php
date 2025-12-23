<x-layouts.app title="Inscriptos por clase">
    <div class="max-w-6xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Inscriptos por clase</h1>

            <div class="flex items-center gap-2">
                @if(!empty($subject))
                    <a href="{{ route('reports.class_enrollees.pdf', ['subject_id' => $subject->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]" target="_blank">PDF</a>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <form action="{{ route('reports.class_enrollees.view') }}" method="GET" class="flex items-center gap-2">
                <label for="subject_id" class="sr-only">Clase</label>
                <select name="subject_id" id="subject_id" class="rounded border px-3 py-2">
                    <option value="">Seleccionar clase</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (!empty($subject) && $subject->id == $c->id) ? 'selected' : '' }}>
                            {{ $c->subjectType->description ?? 'Sin materia' }} — {{ $c->day }} {{ $c->start_time }} (Inscriptos: {{ $c->students_count }})
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-3 py-2 rounded bg-[#29b1dc] text-white">Actualizar</button>
            </form>
        </div>

        @if(empty($subject))
            <div class="p-4 bg-yellow-50 border border-yellow-100 rounded text-sm text-yellow-800">Seleccioná una clase para ver sus inscriptos.</div>
        @else
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
                <div class="mb-2">
                    <div class="text-lg font-semibold">{{ $subject->subjectType->description ?? 'Clase' }}</div>
                    <div class="text-sm text-gray-600">{{ $subject->day }} — {{ $subject->start_time }}{{ $subject->end_time ? ' - ' . $subject->end_time : '' }}</div>
                    <div class="text-sm text-gray-500 mt-1">Inscriptos: {{ $students->count() }}</div>
                </div>

                <div class="overflow-x-auto mt-4">
                    @include('reports.partials._class_enrollees_table', ['printMode' => false])
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>