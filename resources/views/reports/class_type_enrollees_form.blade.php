<x-layouts.app title="Reporte: Inscriptos por tipo de clase">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <h2 class="text-lg font-semibold mb-4">Seleccioná un tipo de clase</h2>

        <form action="{{ route('reports.class_type_enrollees.view') }}" method="GET" class="flex gap-2 items-center">
            <select name="subject_type_id" id="subject_type_id" class="rounded border px-3 py-2" required>
                <option value="">Seleccionar tipo</option>
                @foreach($subjectTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->description }} (Clases: {{ $type->subjects_count }})</option>
                @endforeach
            </select>

            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">Ver</button>
        </form>
    </div>
</x-layouts.app>
