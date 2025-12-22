<x-layouts.app title="Historial de pagos">
    <div class="max-w-6xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Historial de pagos</h1>

            <form method="GET" action="{{ route('payments.history') }}" class="flex items-center gap-2">
                <label for="student_id" class="sr-only">Filtrar por alumno</label>
                <select name="student_id" id="student_id" class="rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2">
                    <option value="">— Todos los alumnos —</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ (string)($studentId ?? '') === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->name }} @if($s->dni) ({{ $s->dni }}) @endif
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-3 py-2 rounded bg-[#29b1dc] text-white hover:bg-[#24a8cf]">Filtrar</button>

                @if(!empty($studentId))
                    <a href="{{ route('payments.history') }}" class="text-sm text-gray-600 dark:text-gray-300 underline ml-2">Quitar filtro</a>
                @endif
            </form>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @if($payments->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                    No se encontraron pagos.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse table-auto">
                        <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Alumno</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Fecha</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Método</th>
                            <th class="text-right text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Monto (AR$)</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Notas</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($payments as $payment)
                            <tr class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->student->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->paymentMethod->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-300">
                                    {{ number_format($payment->amount, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->notes ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $payments->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>