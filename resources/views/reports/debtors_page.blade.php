<x-layouts.app title="Reporte: Alumnos deudores">
    <div class="max-w-6xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Alumnos Deudores</h1>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.debtors.pdf', request()->only('min_debt')) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">PDF</a>
            </div>
        </div>

        <div class="mb-4">
            <form action="{{ route('reports.debtors.view') }}" method="GET" class="flex items-center gap-2">
                <label class="text-sm">Monto mínimo:</label>
                <input type="number" name="min_debt" step="0.01" min="0" value="{{ $min_debt ?? 1 }}" class="rounded border px-2 py-1" />
                <button type="submit" class="px-3 py-2 rounded bg-[#29b1dc] text-white">Actualizar</button>
            </form>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <div class="mb-3 text-sm text-gray-700 dark:text-gray-200 flex items-center justify-between">
                <div>
                    Filtro monto mínimo: <span class="font-semibold">${{ number_format($min_debt ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="text-sm text-gray-500">Generado: {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y H:i') }}</div>
            </div>

            @if($debtors->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                    No se encontraron alumnos con deuda mayor o igual al monto indicado.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase">
                                <th class="px-3 py-2">#</th>
                                <th class="px-3 py-2">Alumno</th>
                                <th class="px-3 py-2">DNI</th>
                                <th class="px-3 py-2">Deuda</th>
                                <th class="px-3 py-2">Periodos adeudados</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($debtors as $i => $s)
                                <tr class="border-t hover:bg-gray-50 dark:hover:bg-zinc-800">
                                    <td class="px-3 py-3">{{ $i + 1 }}</td>
                                    <td class="px-3 py-3">{{ $s->name }}</td>
                                    <td class="px-3 py-3">{{ $s->dni ?? '-' }}</td>
                                    <td class="px-3 py-3">${{ number_format($s->debt, 2, ',', '.') }}</td>
                                    <td class="px-3 py-3">
                                        @if(!empty($s->unpaid_periods))
                                            <ul class="list-disc list-inside">
                                                @foreach($s->unpaid_periods as $up)
                                                    <li>{{ $up['period'] ?? '-' }} — Falta: ${{ number_format($up['deficit'] ?? 0, 2, ',', '.') }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>