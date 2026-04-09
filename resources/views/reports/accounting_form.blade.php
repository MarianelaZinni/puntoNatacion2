<x-layouts.app title="Reporte Contable">
    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Reporte Contable</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Filtrá los pagos registrados por rango de fechas.</p>
            </div>
            <a href="{{ route('reports.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                ← Volver a Reportes
            </a>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <form action="{{ route('reports.accounting.view') }}" method="GET" class="space-y-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha desde
                        </label>
                        <input
                            type="date"
                            id="date_from"
                            name="date_from"
                            value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}"
                            class="block w-full rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                        >
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha hasta
                        </label>
                        <input
                            type="date"
                            id="date_to"
                            name="date_to"
                            value="{{ request('date_to', now()->format('Y-m-d')) }}"
                            class="block w-full rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                        >
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition font-medium">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Generar reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
