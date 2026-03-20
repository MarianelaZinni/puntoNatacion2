<x-layouts.app title="Ver Profesor">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $teacher->name }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('teachers.edit', $teacher) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-yellow-500">
                    <flux:icon name="pencil-square" class="h-5 w-5" />
                    Editar
                </a>
                <a href="{{ route('teachers.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400">
                    <flux:icon name="arrow-left" class="h-5 w-5" />
                    Volver
                </a>
            </div>
        </div>

        {{-- Datos del profesor --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Datos personales</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">DNI</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $teacher->dni ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $teacher->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $teacher->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $teacher->address ?? '—' }}</dd>
                </div>
                @if($teacher->observations)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Observaciones</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $teacher->observations }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Clases como titular --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Clases como Profesor Titular
                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $teacher->titularSubjects->count() }})</span>
            </h2>
            @if($teacher->titularSubjects->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No tiene clases asignadas como titular.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Día</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Horario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($teacher->titularSubjects as $subject)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $subject->subjectType->description ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 capitalize">{{ $subject->day }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $subject->start_time }} - {{ $subject->end_time }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Clases como suplente --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Clases como Profesor Suplente
                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $teacher->suplenteSubjects->count() }})</span>
            </h2>
            @if($teacher->suplenteSubjects->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No tiene clases asignadas como suplente.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Día</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Horario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($teacher->suplenteSubjects as $subject)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $subject->subjectType->description ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 capitalize">{{ $subject->day }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $subject->start_time }} - {{ $subject->end_time }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
