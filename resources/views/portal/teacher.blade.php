<x-layouts.app title="Mi Portal — Profesor">
    <div class="mx-auto max-w-5xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Mi Portal</h1>
            <a href="{{ route('password.edit') }}"
               class="inline-flex items-center gap-2 rounded bg-[#29b1dc] px-4 py-2 text-white transition hover:bg-[#24a8cf]">
                <flux:icon name="key" class="h-4 w-4" />
                Cambiar clave
            </a>
        </div>

        @if (! $teacher)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-center text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                <p class="font-medium">No tienes un perfil de profesor vinculado.</p>
                <p class="mt-1 text-sm">Contactá al administrador para vincular tu usuario con un profesor.</p>
            </div>
        @else
            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Datos personales</h2>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Nombre</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->name }}</dd>
                    </div>
                    @if ($teacher->dni)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">DNI</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->dni }}</dd>
                        </div>
                    @endif
                    @if ($teacher->email)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->email }}</dd>
                        </div>
                    @endif
                    @if ($teacher->phone)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Teléfono</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->phone }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Calendario semanal</h2>
                @if ($calendarSubjects->isEmpty())
                    <p class="text-sm text-gray-400">No tenés clases asignadas.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-zinc-700">
                            <thead class="bg-gray-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs uppercase text-gray-500">Día</th>
                                    <th class="px-3 py-2 text-left text-xs uppercase text-gray-500">Horario</th>
                                    <th class="px-3 py-2 text-left text-xs uppercase text-gray-500">Clase</th>
                                    <th class="px-3 py-2 text-left text-xs uppercase text-gray-500">Rol</th>
                                    <th class="px-3 py-2 text-left text-xs uppercase text-gray-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                @foreach ($calendarSubjects as $subject)
                                    @php
                                        $isTitular = (int) $subject->titular_teacher_id === (int) $teacher->id;
                                        $attendanceUrl = route('attendance.take', ['subject_id' => $subject->id, 'date' => now()->format('Y-m-d')]);
                                    @endphp
                                    <tr onclick="window.location='{{ $attendanceUrl }}'"
                                        class="cursor-pointer transition hover:bg-[#29b1dc]/5 dark:hover:bg-[#29b1dc]/10">
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $subject->day }}</td>
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                            {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                            {{ $subject->subjectType?->description ?? 'Clase' }}
                                            <div class="text-xs text-gray-400">{{ $subject->students_count }} alumno(s)</div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $isTitular ? 'bg-[#29b1dc]/15 text-[#1a8eb5]' : 'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-zinc-100' }}">
                                                {{ $isTitular ? 'Titular' : 'Suplente' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2" onclick="event.stopPropagation()">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('portal.teacher.plans.index', $subject) }}"
                                                   class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 transition hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                                    Contenidos/Clases
                                                </a>
                                                <a href="{{ route('portal.teacher.notes.index', $subject) }}"
                                                   class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 transition hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                                    Notas alumnos
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            
        @endif
    </div>
</x-layouts.app>