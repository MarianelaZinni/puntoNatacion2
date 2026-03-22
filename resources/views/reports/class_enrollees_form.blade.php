<x-layouts.app title="Reporte: Inscriptos por clase">
     <div class="max-w-3xl mx-auto py-8 px-4">
        <h2 class="text-lg font-semibold mb-4">Seleccioná una clase</h2>

        <form id="class-enrollees-form" action="{{ route('reports.class_enrollees.view') }}" method="GET" class="flex gap-2 items-center">
            <select name="subject_id" id="subject_id" class="rounded border px-3 py-2" required>
                <option value="">Seleccionar clase</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->subjectType->description ?? 'Sin materia' }} — {{ $c->day }} {{ $c->start_time }} (Inscriptos: {{ $c->students_count }})</option>
                @endforeach
            </select>

            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">Ver</button>

            <button type="button" id="download-pdf" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]"> PDF</button>
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        const select = document.getElementById('subject_id');
                const btnPdf = document.getElementById('download-pdf');

        btnPdf.addEventListener('click', function () {
            if (!select.value) { alert('Seleccioná una clase'); select.focus(); return; }
            const url = new URL("{{ route('reports.class_enrollees.pdf') }}", window.location.origin);
            url.searchParams.set('subject_id', select.value);
            window.open(url.toString(), '_blank');
        });
    })();
    </script>
    @endpush
</x-layouts.app>