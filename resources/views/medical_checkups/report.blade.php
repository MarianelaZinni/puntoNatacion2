<x-layouts.app title="Revisiones Médicas">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Revisiones Médicas</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Consultá y filtrá las revisiones médicas de todos los alumnos.</p>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('medical_checkups.report') }}"
              class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">

                {{-- Estado --}}
                <div>
                    <label for="approved" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Estado
                    </label>
                    <select id="approved" name="approved"
                            class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                        <option value=""  {{ $filters['approved'] === ''  ? 'selected' : '' }}>Todos</option>
                        <option value="1" {{ $filters['approved'] === '1' ? 'selected' : '' }}>Aprobada</option>
                        <option value="0" {{ $filters['approved'] === '0' ? 'selected' : '' }}>No Aprobada</option>
                    </select>
                </div>

                {{-- Período --}}
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Período (Mes/Año)
                    </label>
                    <input type="month" id="period" name="period"
                           value="{{ $filters['period'] }}"
                           class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                        Filtrar
                    </button>
                    @if($filters['approved'] !== '' || $filters['period'] !== '')
                        <a href="{{ route('medical_checkups.report') }}"
                           class="px-5 py-2 rounded text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 transition">
                            Limpiar
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Resultados --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @if($checkups->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200 text-center">
                    No se encontraron revisiones médicas con los filtros seleccionados.
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ $checkups->count() }} {{ $checkups->count() === 1 ? 'revisión encontrada' : 'revisiones encontradas' }}
                </p>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse table-auto">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-700">
                                <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Alumno</th>
                                <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Período</th>
                                <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Fecha Revisión</th>
                                <th class="text-center text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Estado</th>
                                <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checkups as $checkup)
                                @php
                                    try {
                                        $periodLabel = $checkup->period
                                            ? \Carbon\Carbon::parse($checkup->period)->format('m/Y')
                                            : '-';
                                    } catch (\Throwable $e) {
                                        $periodLabel = '-';
                                    }
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('medical_checkups.index', ['student_id' => $checkup->student_id]) }}"
                                           class="hover:underline text-[#29b1dc]">
                                            {{ $checkup->student_name ?? '-' }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $periodLabel }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $checkup->checkup_date ? $checkup->checkup_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($checkup->approved)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                Aprobada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                No Aprobada
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300 whitespace-pre-line">
                                        {{ $checkup->observations ?: '-' }}
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
