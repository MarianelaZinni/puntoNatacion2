<x-layouts.app title="Períodos de Pausa">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Períodos de Pausa</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Alumno: <span class="font-semibold">{{ $student->name }}</span>
                </p>
            </div>

            <a href="{{ route('students.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-800 dark:text-gray-100 rounded shadow transition">
                Volver al Listado
            </a>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div id="flash-success" class="mb-4 p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/30 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-3 shadow-sm"
                 role="status" aria-live="polite" data-timeout="5000">
                <div class="flex-1 text-base leading-relaxed">{{ session('success') }}</div>
                <button type="button" id="flash-success-close"
                        class="ml-2 -mr-1 p-1 rounded hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none text-green-800 dark:text-green-200"
                        aria-label="Cerrar mensaje">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div id="flash-error" class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200 flex items-start gap-3 shadow-sm"
                 role="alert" aria-live="assertive" data-timeout="8000">
                <div class="flex-1 text-base leading-relaxed">{{ session('error') }}</div>
                <button type="button" id="flash-error-close"
                        class="ml-2 -mr-1 p-1 rounded hover:bg-red-100 dark:hover:bg-red-800 focus:outline-none text-red-800 dark:text-red-200"
                        aria-label="Cerrar mensaje">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Add new pause form --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Agregar Período de Pausa</h2>

            @if($errors->any())
                <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('student_pauses.store') }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">

                <div class="flex-1 min-w-48">
                    <label for="pause_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Período (Mes/Año) <span class="text-red-500">*</span>
                    </label>
                    <input type="month" id="pause_period" name="pause_period" required
                           value="{{ old('pause_period', \Carbon\Carbon::now()->format('Y-m')) }}"
                           class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Durante este mes, el alumno no generará deuda.
                    </p>
                </div>

                <div>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Agregar Pausa
                    </button>
                </div>
            </form>
        </div>

        {{-- Existing pauses list --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Pausas Registradas</h2>

            @if($pauses->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200 text-center">
                    No hay períodos de pausa registrados para este alumno.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse table-auto">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-700">
                                <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Período</th>
                                <th class="text-center text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Estado</th>
                                <th class="text-center text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pauses as $pause)
                                @php $isPast = $pause->isPast(); @endphp
                                <tr class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300 font-medium">
                                        {{ $pause->period_formatted }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($isPast)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300">
                                                Pasado
                                            </span>
                                        @elseif(\Carbon\Carbon::parse($pause->pause_period)->format('Y-m') === \Carbon\Carbon::now()->format('Y-m'))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                Mes actual
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                Próximo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if(!$isPast)
                                                {{-- Edit --}}
                                                <a href="{{ route('student_pauses.edit', $pause) }}"
                                                   title="Editar"
                                                   aria-label="Editar pausa {{ $pause->period_formatted }}"
                                                   class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-yellow-100 dark:hover:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                                                    <span class="sr-only">Editar</span>
                                                    <flux:icon name="pencil-square" class="h-5 w-5" />
                                                </a>

                                                {{-- Delete --}}
                                                <form action="{{ route('student_pauses.destroy', $pause) }}"
                                                      method="POST"
                                                      class="inline pause-delete-form"
                                                      data-period="{{ $pause->period_formatted }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                            onclick="confirmDeletePause(this)"
                                                            title="Eliminar"
                                                            aria-label="Eliminar pausa {{ $pause->period_formatted }}"
                                                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border-0">
                                                        <span class="sr-only">Eliminar</span>
                                                        <flux:icon name="x-circle" class="h-5 w-5" />
                                                    </button>
                                                </form>
                                            @else
                                                {{-- Past: actions disabled --}}
                                                <span title="No se puede editar un período pasado"
                                                      class="inline-flex items-center justify-center h-9 w-9 rounded-full text-gray-300 dark:text-zinc-600 cursor-not-allowed">
                                                    <flux:icon name="pencil-square" class="h-5 w-5" />
                                                </span>
                                                <span title="No se puede eliminar un período pasado"
                                                      class="inline-flex items-center justify-center h-9 w-9 rounded-full text-gray-300 dark:text-zinc-600 cursor-not-allowed">
                                                    <flux:icon name="x-circle" class="h-5 w-5" />
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        window.confirmDeletePause = function (btn) {
            const form = btn.closest('form');
            if (!form) return;
            const period = form.dataset.period || '';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar esta pausa?',
                    html: `<p>Período: <strong>${period}</strong></p><p class="mt-2 text-sm text-gray-600">Esta acción no se puede deshacer.</p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            } else {
                if (confirm('¿Estás seguro de que querés eliminar esta pausa?')) form.submit();
            }
        };

        function initFlash(id, closeId) {
            const el = document.getElementById(id);
            if (!el) return;
            const timeout = parseInt(el.dataset.timeout || 5000, 10);
            const dismiss = () => {
                el.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => { if (el && el.parentNode) el.parentNode.removeChild(el); }, 500);
            };
            const timer = setTimeout(dismiss, timeout);
            const closeBtn = document.getElementById(closeId);
            if (closeBtn) closeBtn.addEventListener('click', () => { clearTimeout(timer); dismiss(); });
        }

        initFlash('flash-success', 'flash-success-close');
        initFlash('flash-error', 'flash-error-close');
    });
    </script>
    @endpush
</x-layouts.app>
