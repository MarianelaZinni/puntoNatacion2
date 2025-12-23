@if(empty($students) || $students->isEmpty())
    <div class="{{ $printMode ?? false ? 'no-data' : 'p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200' }}">
        No hay inscriptos en esta clase.
    </div>
@else
    <table class="{{ $printMode ?? false ? '' : 'w-full text-sm' }}">
        <thead>
            <tr class="{{ $printMode ?? false ? '' : 'text-xs text-gray-500 uppercase' }}">
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}" style="{{ $printMode ?? false ? 'width:40px' : '' }}">#</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">Nombre</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">DNI</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">Email</th>
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">Teléfono</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $st)
                <tr class="{{ $printMode ?? false ? '' : 'border-t hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $i + 1 }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $st->name }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $st->dni ?? '-' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $st->email ?? '-' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $st->phone ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
