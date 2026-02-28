<x-layouts.app title="Profesores">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">PROFESORES</h1>

            <a href="{{ route('teachers.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                <flux:icon name="user-plus" class="h-5 w-5" />
                Nuevo Profesor
            </a>
        </div>

        <div class="mb-4 flex items-center gap-4">
            <label for="teacher-search" class="sr-only">Buscar profesor</label>
            <input
                id="teacher-search"
                type="search"
                placeholder="Buscar por nombre, apellido, email..."
                value="{{ $search ?? '' }}"
                class="w-full max-w-md py-2 px-3 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-600"
            />

            <div class="flex items-center gap-2">
                <button id="clear-search" type="button" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded text-sm text-gray-800 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600">
                    Limpiar
                </button>
            </div>
        </div>

        @if(session('success'))
            <div id="flash-success" class="mb-4 p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/30 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-3 shadow-sm"
                 role="status" aria-live="polite" data-timeout="5000">
                <div class="flex-1">
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

        <div id="teachers-table-wrapper" class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="id" type="button">
                                ID
                                <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="id"></span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="name" type="button">
                                Nombre
                                <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="name"></span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="surname" type="button">
                                Apellido
                                <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="surname"></span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Teléfono
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Clases
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($teachers as $teacher)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $teacher->id }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $teacher->name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $teacher->surname }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $teacher->email ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $teacher->phone ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    {{ $teacher->subjects_count }} {{ $teacher->subjects_count == 1 ? 'clase' : 'clases' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm space-x-2">
                                <a href="{{ route('teachers.show', $teacher) }}"
                                   class="inline-flex items-center px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs transition">
                                    <flux:icon name="eye" class="h-4 w-4 mr-1" />
                                    Ver
                                </a>
                                <a href="{{ route('teachers.edit', $teacher) }}"
                                   class="inline-flex items-center px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-xs transition">
                                    <flux:icon name="pencil" class="h-4 w-4 mr-1" />
                                    Editar
                                </a>
                                <button type="button"
                                        onclick="confirmDelete({{ $teacher->id }}, '{{ $teacher->full_name }}')"
                                        class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs transition">
                                    <flux:icon name="trash" class="h-4 w-4 mr-1" />
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                No se encontraron profesores.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $teachers->links() }}
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('teacher-search');
        const clearBtn = document.getElementById('clear-search');

        if (searchInput) {
            let timeoutId;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    const url = new URL(window.location.href);
                    if (e.target.value) {
                        url.searchParams.set('search', e.target.value);
                    } else {
                        url.searchParams.delete('search');
                    }
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                }, 500);
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                const url = new URL(window.location.href);
                url.searchParams.delete('search');
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        }

        // Sort functionality
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const field = btn.dataset.sort;
                const url = new URL(window.location.href);
                const currentSort = url.searchParams.get('sort');
                const currentDir = url.searchParams.get('direction');

                if (currentSort === field) {
                    url.searchParams.set('direction', currentDir === 'asc' ? 'desc' : 'asc');
                } else {
                    url.searchParams.set('sort', field);
                    url.searchParams.set('direction', 'asc');
                }
                window.location.href = url.toString();
            });
        });

        // Auto-close flash messages
        setTimeout(() => {
            const flash = document.getElementById('flash-success');
            if (flash) flash.style.display = 'none';
        }, 5000);

        document.getElementById('flash-success-close')?.addEventListener('click', () => {
            document.getElementById('flash-success').style.display = 'none';
        });

        // Delete confirmation with SweetAlert2
        function confirmDelete(teacherId, teacherName) {
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Vas a eliminar al profesor <strong>${teacherName}</strong>.<br><br>Las clases que dicta quedarán sin profesor asignado.`,
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
                    form.action = `/teachers/${teacherId}`;
                    
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
