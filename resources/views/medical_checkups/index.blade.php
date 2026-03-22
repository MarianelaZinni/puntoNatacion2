<x-layouts.app title="Revisiones médicas">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Revisiones Médicas</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Alumno: <span class="font-semibold">{{ $student->name }}</span>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('students.show', $student) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-800 dark:text-gray-100 rounded shadow transition">
                    Volver al Alumno
                </a>
                
                <a href="{{ route('medical_checkups.create', ['student_id' => $student->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nueva Revisión
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
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div id="flash-error" class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200 flex items-start gap-3 shadow-sm"
                 role="alert" aria-live="assertive" data-timeout="8000">
                <div class="flex-1 text-base leading-relaxed">
                    {{ session('error') }}
                </div>
                <button type="button"
                        class="ml-2 -mr-1 p-1 rounded hover:bg-red-100 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-red-800 dark:text-red-200"
                        aria-label="Cerrar mensaje" id="flash-error-close">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @if($checkups->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200 text-center">
                    No hay revisiones médicas registradas para este alumno.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse table-auto">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-700">
                                <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Fecha Revisión</th>
                                <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Período</th>
                                <th class="text-center text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Estado</th>
                                <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Observaciones</th>
                                <th class="text-center text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checkups as $checkup)
                                @php
                                    // Formatear período como MM/YYYY
                                    try {
                                        $periodLabel = $checkup->period ? \Carbon\Carbon::parse($checkup->period)->format('m/Y') : '-';
                                    } catch (\Throwable $e) {
                                        $periodLabel = '-';
                                    }
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $checkup->checkup_date ? $checkup->checkup_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $periodLabel }}
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
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $checkup->observations ? Str::limit($checkup->observations, 50) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Botón Ver/Editar --}}
                                            
                                            <a href="{{ route('medical_checkups.edit', $checkup) }}"
                                              title="Editar"
               aria-label="Editar"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-yellow-100 dark:hover:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                <span class="sr-only">Editar</span>

                <flux:icon name="pencil-square" class="h-5 w-5" />
            </a>
                                            
                                            {{-- Formulario Eliminar --}}
                                            <form action="{{ route('medical_checkups.destroy', $checkup) }}" 
                                                  method="POST" 
                                                  class="inline checkup-delete-form"
                                                  data-checkup-date="{{ $checkup->checkup_date ? $checkup->checkup_date->format('d/m/Y') : '' }}"
                                                  data-checkup-period="{{ $periodLabel }}"
                                                  data-checkup-status="{{ $checkup->approved ? 'Aprobada' : 'No Aprobada' }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                        onclick="confirmDeleteCheckup(this)"
                        title="Eliminar {{ $student->name }}"
                        aria-label="Eliminar {{ $student->name }}"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border-0">
                    <span class="sr-only">Eliminar</span>
                    <flux:icon name="x-circle" class="h-5 w-5" />
                </button>
                                                
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($checkups->hasPages())
                    <div class="mt-4">
                        {{ $checkups->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    @push('scripts')    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Confirm delete checkup with SweetAlert2
        window.confirmDeleteCheckup = function (btn) {
            const form = btn.closest('form');
            if (!form) return;

            const checkupDate = form.dataset.checkupDate || '';
            const checkupPeriod = form.dataset.checkupPeriod || '';
            const checkupStatus = form.dataset.checkupStatus || '';
                            Swal.fire({
                    title: '¿Eliminar esta revisión médica?',
                    html: `<div class="text-left">
                        <p class="mb-2">Fecha: <strong>${checkupDate}</strong></p>
                        <p class="mb-2">Periodo: <strong>${checkupPeriod}</strong></p>
                        <p class="mb-2">Estado: <strong>${checkupStatus}</strong></p>
                        <p class="mt-3 text-sm text-gray-600">Esta acción no se puede deshacer.</p>
                    </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
        
        };

        // Flash message auto-dismiss
        function initFlash(id, closeId) {
            const el = document.getElementById(id);
            if (!el) return;

            const timeout = parseInt(el.dataset.timeout || 5000, 10);

            const dismiss = () => {
                el.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => {
                    if (el && el.parentNode) el.parentNode.removeChild(el);
                }, 500);
            };

            const timer = setTimeout(dismiss, timeout);

            const closeBtn = document.getElementById(closeId);
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    clearTimeout(timer);
                    dismiss();
                });
            }
        }

        initFlash('flash-success', 'flash-success-close');
        initFlash('flash-error', 'flash-error-close');
    });
    </script>
    @endpush
</x-layouts.app>