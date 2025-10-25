<x-layouts.app title="Ver alumno">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Datos del Alumno</h1>
        </div>

        {{-- Flash success (auto-dismiss + close) --}}
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

        {{-- Flash error (auto-dismiss + close) --}}
        @if(session('error'))
            <div id="flash-error"
                 class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200 flex items-start gap-3 shadow-sm"
                 role="alert" aria-live="assertive" data-timeout="8000">
                <div class="flex-1 text-base leading-relaxed">
                    {{ session('error') }}
                </div>

                <button type="button"
                        class="ml-2 -mr-1 p-1 rounded hover:bg-red-100 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-red-800 dark:text-red-200"
                        aria-label="Cerrar mensaje" id="flash-error-close">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Detalles --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <!-- Increased vertical spacing between rows (gap-y-8) and larger spacing between dt/dd (dd mt-3) -->
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-8 gap-x-6 text-base">
                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">ID</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->id }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">DNI / Documento</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->dni ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Nombre</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->name }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Email</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->email ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Teléfono</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->phone ?? '-' }}</dd>
                </div>

                

                <div class="sm:col-span-2">
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Dirección</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->address ?? '-' }}</dd>
                </div>

               

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Creado</dt>
                    <dd class="mt-3 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">{{ $student->created_at ? $student->created_at->diffForHumans() . ' — ' . $student->created_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Última actualización</dt>
                    <dd class="mt-3 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">{{ $student->updated_at ? $student->updated_at->diffForHumans() . ' — ' . $student->updated_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>
            </dl>

            {{-- Actions (below details, same style as edit) --}}
            <div class="mt-6 pt-3 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('students.index') }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition text-base">
                    Volver
                </a>

                <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition text-base">
                    Editar
                </a>

                <form action="{{ route('students.destroy', $student) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            onclick="confirmDelete(this)"
                            title="Eliminar {{ $student->name }}"
                            aria-label="Eliminar {{ $student->name }}"
                            class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition text-base">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- SweetAlert2 (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Confirm delete helper: asks confirm via SweetAlert2, then submits the closest form
        window.confirmDelete = function (btn) {
            // find closest form
            const form = btn.closest('form');
            if (!form) return;

            // Use SweetAlert2 if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e', // rojo para eliminar
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
                return;
            }

            // Fallback to native confirm
            if (confirm('¿Seguro que querés eliminar este alumno? Esta acción no se puede deshacer.')) {
                form.submit();
            }
        };

        // Reusable flash init (auto-dismiss + close)
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

            el.addEventListener('focusin', function () {
                clearTimeout(timer);
            });
        }

        initFlash('flash-success', 'flash-success-close');
        initFlash('flash-error', 'flash-error-close');
    });
    </script>
    @endpush
</x-layouts.app>