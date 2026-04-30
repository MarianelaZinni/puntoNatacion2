<x-layouts.app title="Asistencia">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Pasar Lista</h1>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form: select class + date --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-8">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Seleccionar clase y fecha</h2>

            <form action="{{ route('attendance.take') }}" method="GET" class="flex flex-wrap items-end gap-4">

                <div class="flex-1 min-w-[220px]">
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Clase</label>
                    <select name="subject_id" id="subject_id" required
                            class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] text-sm">
                        <option value="">Seleccionar clase...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">
                                {{ $subject->subjectType->description ?? 'Sin tipo' }}
                                — {{ $subject->day }}
                                {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[160px]">
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                    <input
                        type="date"
                        name="date"
                        id="date"
                        required
                         value="{{ $today }}"
                        max="{{ $today }}"
                        class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] text-sm"
                    >
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition text-sm">
                    Tomar lista
                </button>
            </form>
        </div>

        {{-- All sessions --}}
        @if($recent->count())
        @php
            $toggleDir = fn($col) => ($sortBy === $col && $direction === 'desc') ? 'asc' : 'desc';
            $arrow     = fn($col) => $sortBy === $col ? ($direction === 'asc' ? ' ↑' : ' ↓') : '';
            $sortUrl   = fn($col) => route('attendance.index', ['sort' => $col, 'direction' => $toggleDir($col)]);
        @endphp
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Listas de asistencia</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                <a href="{{ $sortUrl('id') }}" class="hover:text-gray-700 dark:hover:text-gray-100"># {{ $arrow('id') }}</a>
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                <a href="{{ $sortUrl('date') }}" class="hover:text-gray-700 dark:hover:text-gray-100">Fecha{{ $arrow('date') }}</a>
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Clase</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Presentes / Total</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($recent as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-2 text-gray-400 dark:text-gray-500 font-mono text-xs">
                                {{ $row->id }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">
                                {{ $row->date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200">
                                @if($row->subject)
                                    {{ $row->subject->subjectType->description ?? 'Sin tipo' }}
                                    — {{ $row->subject->day }}
                                    {{ substr($row->subject->start_time, 0, 5) }}–{{ substr($row->subject->end_time, 0, 5) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="font-semibold text-green-700 dark:text-green-400">{{ $row->present_count }}</span>
                                <span class="text-gray-500 dark:text-gray-400">/ {{ $row->total }}</span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('attendance.take', ['subject_id' => $row->subject_id, 'date' => $row->date->format('Y-m-d')]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 rounded text-xs text-white bg-[#29b1dc] hover:bg-[#24a8cf] transition">
                                        Ver / Editar
                                    </a>
                                    <form action="{{ route('attendance.destroy', $row) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar la lista #{{ $row->id }} del {{ $row->date->format('d/m/Y') }}? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded text-xs text-white bg-red-500 hover:bg-red-600 transition">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recent->hasPages())
            <div class="mt-4">
                {{ $recent->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</x-layouts.app>