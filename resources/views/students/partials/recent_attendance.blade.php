{{-- Asistencia reciente: últimas 10 clases del alumno --}}
<div class="mt-8">
    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Asistencia reciente (últimas 10 clases)</h2>

    @if($recentAttendance->isEmpty())
        <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-200 dark:border-zinc-700 text-sm text-gray-500 dark:text-gray-400">
            No hay registros de asistencia para este alumno todavía.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-zinc-700">
                        <th class="px-3 py-2">Fecha</th>
                        <th class="px-3 py-2">Clase</th>
                        <th class="px-3 py-2">Horario</th>
                        <th class="px-3 py-2 text-center">Asistencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-700">
                    @foreach($recentAttendance as $record)
                        @php
                            $subj = $record->subject;
                            $typeLabel = $subj?->subjectType?->description ?? ($subj?->subjectType?->value ?? 'Clase');
                            $schedule = $subj
                                ? (substr($subj->start_time ?? '', 0, 5) . '–' . substr($subj->end_time ?? '', 0, 5))
                                : '—';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-200">
                                {{ $record->date instanceof \Carbon\Carbon
                                    ? $record->date->format('d/m/Y')
                                    : \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-200">
                                {{ $typeLabel }}
                                @if($subj?->day)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">({{ $subj->day }})</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-gray-500 dark:text-gray-400">{{ $schedule }}</td>
                            <td class="px-3 py-3 text-center">
                                @if($record->present)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Presente
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Ausente
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
