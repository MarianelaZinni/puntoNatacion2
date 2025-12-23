<x-layouts.app title="Reporte: Pagos por alumno">
    <div class="max-w-3xl mx-auto py-8">
        <h2 class="text-lg font-semibold mb-4">Pagos por alumno</h2>

        <form id="payments-by-student-form" action="{{ route('reports.payments_by_student.view') }}" method="GET" class="space-y-3">
            <!-- Fila 1: selector de alumno -->
            <div class="flex items-center gap-2">
                <label for="student_id" class="sr-only">Alumno</label>
                <select name="student_id" id="student_id" class="w-full rounded border px-3 py-2">
                    <option value="">Seleccionar alumno (opcional)</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} @if($s->dni) ({{ $s->dni }}) @endif</option>
                    @endforeach
                </select>
            </div>

            <!-- Fila 2: desde / hasta (debajo del selector) -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2">
                    <label for="period_from" class="text-sm">Desde</label>
                    <input id="period_from" type="month" name="period_from" class="rounded border px-2 py-1" />
                </div>

                <div class="flex items-center gap-2">
                    <label for="period_to" class="text-sm">Hasta</label>
                    <input id="period_to" type="month" name="period_to" class="rounded border px-2 py-1" />
                </div>

                <!-- Acciones -->
                <div class="ml-auto flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                        Ver
                    </button>

                    <button type="button" id="download-pdf"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                        PDF
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        const form = document.getElementById('payments-by-student-form');
        const downloadPdf = document.getElementById('download-pdf');

        downloadPdf.addEventListener('click', function () {
            const params = new URLSearchParams(new FormData(form));
            const url = new URL("{{ route('reports.payments_by_student.pdf') }}", window.location.origin);
            url.search = params.toString();
            window.open(url.toString(), '_blank');
        });
    })();
    </script>
    @endpush
</x-layouts.app>