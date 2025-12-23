<x-layouts.app title="Todos los alumnos">
    <div class="max-w-6xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Todos los alumnos</h1>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.all_students.pdf') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">PDF</a>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <div class="mb-3 text-sm text-gray-700 dark:text-gray-200 flex items-center justify-between">
                <div>
                    Listado completo de alumnos
                </div>
                <div class="text-sm text-gray-500">Generado: {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y H:i') }}</div>
            </div>

            @if($students->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                    No hay alumnos registrados.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase">
                                <th class="px-3 py-2">#</th>
                                <th class="px-3 py-2">Nombre</th>
                                <th class="px-3 py-2">DNI</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Teléfono</th>
                                <th class="px-3 py-2">Clases inscritas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $i => $s)
                                <tr class="border-t hover:bg-gray-50 dark:hover:bg-zinc-800">
                                    <td class="px-3 py-3">{{ $i + 1 }}</td>
                                    <td class="px-3 py-3">{{ $s->name }}</td>
                                    <td class="px-3 py-3">{{ $s->dni ?? '-' }}</td>
                                    <td class="px-3 py-3">{{ $s->email ?? '-' }}</td>
                                    <td class="px-3 py-3">{{ $s->phone ?? '-' }}</td>
                                    <td class="px-3 py-3">
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
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>