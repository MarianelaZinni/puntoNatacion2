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

                {{-- Period filter: desde / hasta (type="month") --}}
                <label for="period_from" class="sr-only">Periodo desde</label>
                <input type="month" id="period_from" name="period_from" value="{{ request('period_from') ?? '' }}"
                       class="rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2"
                       title="Periodo desde (YYYY-MM)">

                <label for="period_to" class="sr-only">Periodo hasta</label>
                <input type="month" id="period_to" name="period_to" value="{{ request('period_to') ?? '' }}"
                       class="rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2"
                       title="Periodo hasta (YYYY-MM)">

                <button type="submit" class="px-3 py-2 rounded bg-[#29b1dc] text-white hover:bg-[#24a8cf]">Filtrar</button>

                @if(!empty($studentId) || request()->hasAny(['period_from','period_to']))
                    <a href="{{ route('payments.history') }}" class="text-sm text-gray-600 dark:text-gray-300 underline ml-2">Quitar filtro</a>
                @endif
            </form>
        </div>

        {{-- Banner indicador de deuda para el alumno seleccionado --}}
        @if(!empty($selectedStudent) && is_array($debtSummary))
            @php
                $debtAmount = $debtSummary['debt'] ?? 0.0;
                $monthlyAmount = $debtSummary['monthly_amount'] ?? 0.0;
                $nextPeriod = $debtSummary['next_unpaid_period'] ?? null;
            @endphp

            <div class="mb-4">
                @if($debtAmount > 0)
                    <div class="p-3 rounded border border-red-200 bg-red-50 text-red-800 flex items-center justify-between">
                        <div>
                            <strong>El alumno posee deuda:</strong>
                            <span class="ml-2 font-semibold">${{ number_format($debtAmount, 2, ',', '.') }}</span>
                            @if($nextPeriod)
                                <span class="ml-3 text-sm">Próximo periodo impago: <span class="font-medium">{{ $nextPeriod }}</span></span>
                            @endif
                        </div>
                        <div class="text-sm text-red-800">Alumno: <span class="font-medium">{{ $selectedStudent->name }}</span></div>
                    </div>
                @else
                    <div class="p-3 rounded border border-green-200 bg-green-50 text-green-800 flex items-center justify-between">
                        <div>
                            <strong>No posee deuda:</strong>
                            <span class="ml-2 text-sm">Total abonado: <span class="font-semibold">${{ number_format($debtSummary['total_paid'] ?? 0, 2, ',', '.') }}</span></span>
                        </div>
                        <div class="text-sm text-green-800">Alumno: <span class="font-medium">{{ $selectedStudent->name }}</span></div>
                    </div>
                @endif
            </div>
        @endif

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
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Periodo</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Método</th>
                            <th class="text-right text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Monto (AR$)</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Notas</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($payments as $payment)
                            @php
                                // Resolvemos el periodo a mostrar: preferimos payment_period, si no existe usamos payment_date.
                                $periodSource = $payment->payment_period ?? $payment->payment_date;
                                try {
                                    $periodLabel = $periodSource ? \Carbon\Carbon::parse($periodSource)->format('m/Y') : '-';
                                } catch (\Throwable $e) {
                                    $periodLabel = '-';
                                }
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->student->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}
                                </td>

                                {{-- Columna: Periodo --}}
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $periodLabel }}
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

                {{-- Paginación --}}
                @if(method_exists($payments, 'links'))
                    <div class="mt-4">
                        {{ $payments->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts.app>