@if(empty($debtors) || $debtors->isEmpty())
    <div class="{{ $printMode ?? false ? 'no-data' : 'p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200' }}">
        No se encontraron alumnos con deuda mayor o igual al monto indicado.
    </div>
@else
    <table class="{{ $printMode ?? false ? '' : 'w-full text-sm' }}">
        <thead>
            <tr class="{{ $printMode ?? false ? '' : 'text-xs text-gray-500 uppercase' }}">
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}" style="{{ $printMode ?? false ? 'width:40px' : '' }}">#</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">Alumno</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">DNI</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">Deuda</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">Periodos adeudados</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debtors as $i => $s)
                <tr class="{{ $printMode ?? false ? '' : 'border-t hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $i + 1 }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $s->name }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $s->dni ?? '-' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">${{ number_format($s->debt, 2, ',', '.') }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">
                        @if(!empty($s->unpaid_periods))
                            <ul class="{{ $printMode ?? false ? '' : 'list-disc list-inside' }}">
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
@endif