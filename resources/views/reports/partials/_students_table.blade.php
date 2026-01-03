@if(empty($students) || $students->isEmpty())
    <div class="{{ $printMode ?? false ? 'no-data' : 'p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200' }}">
        No hay alumnos registrados.
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
                <th class="{{ $printMode ?? false ? '' : 'px-3 py-2' }}">Clases inscritas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $s)
                <tr class="{{ $printMode ?? false ? '' : 'border-t hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $i + 1 }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $s->name }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $s->dni ?? '-' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $s->email ?? '-' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">{{ $s->phone ?? '-' }}</td>
                    <td class="{{ $printMode ?? false ? '' : 'px-3 py-3' }}">
                        @if(!empty($s->subjects) && $s->subjects->count())
                            {{ $s->subjects->pluck('subjectType.description')->filter()->implode(', ') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif