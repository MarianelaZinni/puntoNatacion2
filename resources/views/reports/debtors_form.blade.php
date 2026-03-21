<x-layouts.app title="Reporte: Alumnos deudores">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <h2 class="text-lg font-semibold mb-4">Alumnos deudores</h2>

        <form id="debtors-form" action="{{ route('reports.debtors.view') }}" method="GET" class="flex items-center gap-2">
            <label class="text-sm">Monto mínimo:</label>
            <input type="number" name="min_debt" step="0.01" min="0" value="{{ request('min_debt', 1) }}" class="rounded border px-2 py-1" />

            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">Ver</button>

            <button type="button" id="download-pdf" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">PDF</button>
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        const form = document.getElementById('debtors-form');
        const inputMin = form.querySelector('input[name="min_debt"]');
        const btnPdf = document.getElementById('download-pdf');

        btnPdf.addEventListener('click', function () {
            const min = inputMin.value || 1;
            const url = new URL("{{ route('reports.debtors.pdf') }}", window.location.origin);
            url.searchParams.set('min_debt', min);
            window.open(url.toString(), '_blank');
        });
    })();
    </script>
    @endpush
</x-layouts.app>