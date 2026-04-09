<x-layouts.app title="Reporte Contable">
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Reporte Contable</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($date_from || $date_to)
                        Período:
                        <span class="font-medium text-gray-700 dark:text-gray-200">
                            {{ $date_from ? \Carbon\Carbon::parse($date_from)->format('d/m/Y') : '—' }}
                            al
                            {{ $date_to ? \Carbon\Carbon::parse($date_to)->format('d/m/Y') : '—' }}
                        </span>
                    @else
                        Todos los pagos registrados
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('reports.accounting.form') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    ← Cambiar filtros
                </a>

                @if($payments->isNotEmpty())
                    <a href="{{ route('reports.accounting.excel', array_filter(['date_from' => $date_from, 'date_to' => $date_to])) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500 transition font-medium text-sm shadow-sm"
                       title="Descargar como archivo Excel (.xlsx)">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Exportar a Excel
                    </a>
                @endif
            </div>
        </div>

        {{-- Filter form (inline) --}}
        <div class="mb-5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg px-4 py-3 shadow-sm">
            <form action="{{ route('reports.accounting.view') }}" method="GET"
                  class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[140px]">
                    <label for="date_from" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Desde</label>
                    <input type="date" id="date_from" name="date_from"
                           value="{{ $date_from ?? '' }}"
                           class="block w-full rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#29b1dc]">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label for="date_to" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Hasta</label>
                    <input type="date" id="date_to" name="date_to"
                           value="{{ $date_to ?? '' }}"
                           class="block w-full rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#29b1dc]">
                </div>
                <button type="submit"
                        class="px-4 py-1.5 rounded-lg bg-[#29b1dc] text-white text-sm hover:bg-[#24a8cf] transition focus:outline-none focus:ring-2 focus:ring-[#29b1dc]">
                    Actualizar
                </button>
            </form>
        </div>

        {{-- Results --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">

            @if($payments->isEmpty())
                <div class="p-10 text-center text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>No se encontraron pagos para el período seleccionado.</p>
                </div>
            @else
                {{-- Summary bar --}}
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $payments->count() }}</span>
                        pago(s) encontrado(s)
                    </div>
                    <div class="text-sm font-semibold">
                        Total:
                        <span class="text-lg text-[#29b1dc]">${{ number_format((float)$total, 2, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alumno</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">DNI</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha de Pago</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monto</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Medio de Pago</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($payments as $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                    {{ $p->student->name ?? '—' }}
                                    <span class="sm:hidden block text-xs text-gray-500 font-normal">{{ $p->student->dni ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 hidden sm:table-cell">{{ $p->student->dni ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                    ${{ number_format((float)$p->amount, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300 hidden md:table-cell">{{ $p->paymentMethod->name ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-zinc-800 border-t-2 border-gray-300 dark:border-zinc-600">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-800 dark:text-gray-100 uppercase">TOTAL</td>
                                <td class="hidden sm:table-cell"></td>
                                <td class="px-4 py-3 text-right text-base font-bold text-[#29b1dc]">
                                    ${{ number_format((float)$total, 2, ',', '.') }}
                                </td>
                                <td class="hidden md:table-cell"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Footer note --}}
                <div class="px-4 py-2 text-xs text-gray-400 dark:text-gray-500 border-t border-gray-100 dark:border-zinc-800">
                    Generado el {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y \a \l\a\s H:i') }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
