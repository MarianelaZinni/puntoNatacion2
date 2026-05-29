<x-layouts.app title="Mi Perfil — Profesor">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Mi Perfil</h1>
            <a href="{{ route('password.edit') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] transition">
                <flux:icon name="key" class="h-4 w-4" />
                Cambiar clave
            </a>
        </div>

        @if(!$teacher)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-6 text-center text-amber-800 dark:text-amber-300">
                <p class="font-medium">No tienes un perfil de profesor vinculado.</p>
                <p class="text-sm mt-1">Contactá al administrador para vincular tu usuario con un profesor.</p>
            </div>
        @else
            {{-- Profile card --}}
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Datos personales</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Nombre</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->name }}</dd>
                    </div>
                    @if($teacher->dni)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">DNI</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->dni }}</dd>
                    </div>
                    @endif
                    @if($teacher->email)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->email }}</dd>
                    </div>
                    @endif
                    @if($teacher->phone)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Teléfono</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->phone }}</dd>
                    </div>
                    @endif
                    @if($teacher->address)
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">Dirección</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $teacher->address }}</dd>
                    </div>
                    @endif
                    @if($teacher->observations)
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">Observaciones</dt>
                        <dd class="text-gray-700 dark:text-gray-300">{{ $teacher->observations }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Titular classes --}}
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Clases como Profesor Titular
                </h2>
                @if($teacher->titularSubjects->isEmpty())
                    <p class="text-sm text-gray-400">No tenés clases asignadas como titular.</p>
                @else
                    <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
                        @foreach($teacher->titularSubjects as $subject)
                        <li class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $subject->subjectType?->description ?? 'Clase' }}
                                </p>
                                <p class="text-gray-500 dark:text-gray-400">
                                    {{ $subject->day }} · {{ substr($subject->start_time, 0, 5) }} – {{ substr($subject->end_time, 0, 5) }}
                                </p>
                            </div>
                            <a href="{{ route('attendance.index') }}"
                               class="inline-flex items-center gap-1 self-start rounded bg-[#29b1dc] px-3 py-1.5 text-xs text-white transition hover:bg-[#24a8cf] sm:self-auto">
                                <flux:icon name="clipboard-document-check" class="h-3.5 w-3.5" />
                                Asistencia
                            </a>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Suplente classes --}}
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Clases como Profesor Suplente
                </h2>
                @if($teacher->suplenteSubjects->isEmpty())
                    <p class="text-sm text-gray-400">No tenés clases asignadas como suplente.</p>
                @else
                    <ul class="divide-y divide-gray-100 dark:divide-zinc-800">
                        @foreach($teacher->suplenteSubjects as $subject)
                        <li class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $subject->subjectType?->description ?? 'Clase' }}
                                </p>
                                <p class="text-gray-500 dark:text-gray-400">
                                    {{ $subject->day }} · {{ substr($subject->start_time, 0, 5) }} – {{ substr($subject->end_time, 0, 5) }}
                                </p>
                            </div>
                            <a href="{{ route('attendance.index') }}"
                               class="inline-flex items-center gap-1 self-start rounded bg-[#29b1dc] px-3 py-1.5 text-xs text-white transition hover:bg-[#24a8cf] sm:self-auto">
                                <flux:icon name="clipboard-document-check" class="h-3.5 w-3.5" />
                                Asistencia
                            </a>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Attendance link --}}
            <div class="mt-6 text-center">
                <a href="{{ route('attendance.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded bg-[#29b1dc] text-white hover:bg-[#24a8cf] transition font-medium">
                    <flux:icon name="clipboard-document-check" class="h-5 w-5" />
                    Tomar Asistencia
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>