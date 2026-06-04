<x-layouts.app title="Inscriptos por tipo de clase">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Inscriptos por tipo de clase</h1>

            <div class="flex items-center gap-2">
                @if(!empty($subject_type))
                    <a href="{{ route('reports.class_type_enrollees.pdf', ['subject_type_id' => $subject_type->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]" target="_blank">PDF</a>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <form action="{{ route('reports.class_type_enrollees.view') }}" method="GET" class="flex items-center gap-2">
                <label for="subject_type_id" class="sr-only">Tipo de clase</label>
                <select name="subject_type_id" id="subject_type_id" class="rounded border px-3 py-2">
                    <option value="">Seleccionar tipo</option>
                    @foreach($subject_types as $type)
                        <option value="{{ $type->id }}" {{ (!empty($subject_type) && $subject_type->id == $type->id) ? 'selected' : '' }}>
                            {{ $type->description }} (Clases: {{ $type->subjects_count }})
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-3 py-2 rounded bg-[#29b1dc] text-white">Actualizar</button>
            </form>
        </div>

        @if(empty($subject_type))
            <div class="p-4 bg-yellow-50 border border-yellow-100 rounded text-sm text-yellow-800">Seleccioná un tipo de clase para ver sus alumnos.</div>
        @else
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
                <div class="mb-2">
                    <div class="text-lg font-semibold">{{ $subject_type->description }}</div>
                    <div class="text-sm text-gray-600">Clases de este tipo: {{ $classes_count }}</div>
                    <div class="text-sm text-gray-500 mt-1">Alumnos únicos inscriptos: {{ $students->count() }}</div>
                </div>

                <div class="overflow-x-auto mt-4">
                    @include('reports.partials._class_enrollees_table', ['printMode' => false])
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>