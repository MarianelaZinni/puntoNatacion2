<x-layouts.app title="Comunicados">
    <div class="max-w-5xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Comunicados</h1>
            <a href="{{ route('announcements.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Comunicado
            </a>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div id="flash-success" class="mb-4 p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/30 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-3 shadow-sm"
                 role="status" aria-live="polite" data-timeout="5000">
                <div class="flex-1 text-base leading-relaxed">{{ session('success') }}</div>
                <button type="button" id="flash-success-close"
                        class="ml-2 -mr-1 p-1 rounded hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-green-800 dark:text-green-200"
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
                        class="ml-2 -mr-1 p-1 rounded hover:bg-red-100 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-red-800 dark:text-red-200"
                        aria-label="Cerrar mensaje">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
            @if($announcements->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    No hay comunicados creados todavía.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Título</th>
                                <th class="hidden md:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Creado por</th>
                                <th class="hidden sm:table-cell px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-800">
                            @foreach($announcements as $announcement)
                            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                                <td class="min-w-[16rem] px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <span class="break-words">{{ $announcement->title }}</span>
                                    <p class="mt-0.5 text-xs font-normal text-gray-500 dark:text-gray-400">{{ Str::limit($announcement->body, 80) }}</p>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $announcement->author?->name ?? '—' }}
                                </td>
                                <td class="hidden sm:table-cell px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $announcement->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($announcement->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-gray-400">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('announcements.edit', $announcement) }}"
                                           title="Editar"
                                           class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-yellow-100 dark:hover:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                                            <flux:icon name="pencil-square" class="h-5 w-5" />
                                            <span class="sr-only">Editar</span>
                                        </a>

                                        <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline announcement-delete-form"
                                              data-title="{{ $announcement->title }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="confirmDeleteAnnouncement(this)"
                                                    title="Eliminar"
                                                    class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border-0">
                                                <flux:icon name="x-circle" class="h-5 w-5" />
                                                <span class="sr-only">Eliminar</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($announcements->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-zinc-700">
                        {{ $announcements->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        window.confirmDeleteAnnouncement = function (btn) {
            const form = btn.closest('form');
            if (!form) return;
            const title = form.dataset.title || '';
            Swal.fire({
                title: '¿Eliminar este comunicado?',
                html: `<p class="text-left">Título: <strong>${title}</strong></p><p class="text-sm text-gray-600 mt-2">Esta acción no se puede deshacer.</p>`,
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
