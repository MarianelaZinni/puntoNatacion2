@if(empty($payments) || $payments->isEmpty())
    <div class="{{ $printMode ?? false ? 'no-data' : 'p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200' }}">
        No se encontraron pagos con los filtros seleccionados.
    </div>
@else
    <table class="{{ $printMode ?? false ? '' : 'w-full text-sm' }}">
        <thead>
            <tr class="{{ $printMode ?? false ? '' : 'text-xs text-gray-500 uppercase' }}">
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2 text-left' }}">Fecha</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2 text-left' }}">Periodo</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2 text-left' }}">Alumno</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2 text-left' }}">Método</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2 text-right' }}">Monto</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2 text-left' }}">Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
                @php
                    $periodSource = $p->payment_period ?? $p->payment_date;
                    try { $periodLabel = $periodSource ? \Carbon\Carbon::parse($periodSource)->format('m/Y') : '-'; } catch (\Throwable $e) { $periodLabel = '-'; }
                @endphp
                <tr class="{{ $printMode ?? false ? '' : 'border-t hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') : '-' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $periodLabel }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $p->student->name ?? '-' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $p->paymentMethod->name ?? 'N/A' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3 text-right' }}">${{ number_format($p->amount,2,',','.') }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $p->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
