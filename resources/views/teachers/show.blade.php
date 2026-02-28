<x-layouts.app title="Ver profesor">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Datos del Profesor</h1>
            <div class="flex gap-2">
                <a href="{{ route('teachers.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded shadow transition">
                    <flux:icon name="arrow-left" class="h-4 w-4" />
                    Volver
                </a>
                <a href="{{ route('teachers.edit', $teacher) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded shadow transition">
                    <flux:icon name="pencil" class="h-4 w-4" />
                    Editar
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div id="flash-success" class="mb-4 p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/30 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-3 shadow-sm"
                 role="status" aria-live="polite" data-timeout="5000">
                <div class="flex-1 text-base leading-relaxed">
                    {{ session('success') }}
                </div>
                <button type="button"
                        class="ml-2 -mr-1 p-1 rounded hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-green-800 dark:text-green-200"
                        aria-label="Cerrar mensaje" id="flash-success-close">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Detalles del profesor --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Información Personal</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-6 text-base">
                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">ID</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->id }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Nombre Completo</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->full_name }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Email</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->email ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Teléfono</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->phone ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Fecha de Nacimiento</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">
                        {{ $teacher->birth_date ? $teacher->birth_date->format('d/m/Y') : '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Edad</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">
                        @if($teacher->age)
                            {{ $teacher->age }} {{ $teacher->age === 1 ? 'año' : 'años' }}
                        @else
                            -
                        @endif
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Dirección</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->address ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Clases que dicta --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Clases que Dicta
                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">({{ $teacher->subjects->count() }})</span>
            </h2>

            @if($teacher->subjects->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Día
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Horario
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Capacidad
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Alumnos
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($teacher->subjects as $subject)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->subjectType->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ ucfirst($subject->day) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->capacity }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                            {{ $subject->students->count() }} / {{ $subject->capacity }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                    Este profesor aún no tiene clases asignadas.
                </p>
            @endif
        </div>

        {{-- Botón de eliminar --}}
        <div class="mt-6 flex justify-end">
            <button type="button"
                    onclick="confirmDelete()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded shadow transition">
                <flux:icon name="trash" class="h-4 w-4" />
                Eliminar Profesor
            </button>
        </div>
    </div>

    <script>
        // Auto-close flash messages
        setTimeout(() => {
            const flash = document.getElementById('flash-success');
            if (flash) flash.style.display = 'none';
        }, 5000);

        document.getElementById('flash-success-close')?.addEventListener('click', () => {
            document.getElementById('flash-success').style.display = 'none';
        });

        // Delete confirmation
        function confirmDelete() {
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Vas a eliminar al profesor <strong>{{ $teacher->full_name }}</strong>.<br><br>
                       @if($teacher->subjects->count() > 0)
                       Las {{ $teacher->subjects->count() }} {{ $teacher->subjects->count() == 1 ? 'clase' : 'clases' }} que dicta quedarán sin profesor asignado.
                       @endif`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('teachers.destroy', $teacher) }}';
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    
                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</x-layouts.app>
