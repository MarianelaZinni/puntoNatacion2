<x-layouts.app title="Alumnos">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">ALUMNOS</h1>

            <a href="{{ route('students.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                 <flux:icon name="user-plus" class="h-5 w-5" />
                Nuevo Alumno
            </a>
        </div>

        <div class="mb-4 flex items-center gap-4">
            <label for="student-search" class="sr-only">Buscar por nombre</label>
            <input
                id="student-search"
                type="search"
                placeholder="Buscar por nombre..."
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

        <!-- Close button -->
        <button type="button"
                class="ml-2 -mr-1 p-1 rounded hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-green-800 dark:text-green-200"
                aria-label="Cerrar mensaje" id="flash-success-close">
            <!-- simple X icon -->
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div id="students-table-wrapper" class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
    <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="id" type="button" aria-sort="none">
        ID
        <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="id"></span>
    </button>
</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
    <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="dni" type="button" aria-sort="none">
        DNI
        <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="dni"></span>
    </button>
</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
    <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="name" type="button" aria-sort="none">
        Nombre
        <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="name"></span>
    </button>
</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
    <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="email" type="button" aria-sort="none">
        Mail
        <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="email"></span>
    </button>
</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>

                <tbody id="students-table-body" class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                    @include('students.partials.rows', ['students' => $students])
                </tbody>
            </table>

            <div id="students-pagination" class="p-4">
                @include('students.partials.pagination', ['students' => $students])
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    (function () {
        const searchInput = document.getElementById('student-search');
        const clearBtn = document.getElementById('clear-search');
        const tableBody = document.getElementById('students-table-body');
        const paginationDiv = document.getElementById('students-pagination');
        const sortButtons = document.querySelectorAll('.sort-btn');

        // Estado actual
        let state = {
            search: '{{ $search ?? "" }}',
            sort: '{{ $sort ?? "id" }}',
            direction: '{{ $direction ?? "asc" }}',
            page: 1
        };

        // Debounce helper
        function debounce(fn, delay) {
            let t;
            return function (...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        // Construye la URL con query string para la petición
        function buildUrl(overrides = {}) {
            const params = new URLSearchParams(window.location.search);
            const merged = Object.assign({}, state, overrides);
            if (merged.search) params.set('search', merged.search); else params.delete('search');
            if (merged.sort) params.set('sort', merged.sort); else params.delete('sort');
            if (merged.direction) params.set('direction', merged.direction); else params.delete('direction');
            if (merged.page) params.set('page', merged.page); else params.delete('page');
            return `${window.location.pathname}?${params.toString()}`;
        }

        // Actualiza los indicadores en las cabeceras
        function updateSortIndicators() {
            document.querySelectorAll('.sort-indicator').forEach(el => {
                const field = el.getAttribute('data-field');
                if (field === state.sort) {
                    el.innerHTML = state.direction === 'asc' ? '▲' : '▼';
                } else {
                    el.innerHTML = '';
                }
            });
            // Accessibility: set aria-sort on buttons
    document.querySelectorAll('.sort-btn').forEach(btn => {
        const field = btn.getAttribute('data-sort');
        if (field === state.sort) {
            btn.setAttribute('aria-sort', state.direction === 'asc' ? 'ascending' : 'descending');
        } else {
            btn.setAttribute('aria-sort', 'none');
        }
    });
        }

        // Fetch los fragmentos rows + pagination desde el servidor
        async function fetchAndRender(overrides = {}) {
            const url = buildUrl(overrides);
            // Actualiza la URL en la barra (no recarga)
            history.pushState({}, '', url);

            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    console.error('Error en la petición', res.status);
                    return;
                }

                const data = await res.json();
                // Reemplazamos contenido
                if (data.rows !== undefined) tableBody.innerHTML = data.rows;
                if (data.pagination !== undefined) paginationDiv.innerHTML = data.pagination;

                // Re-attach handlers for new pagination links and delete buttons
                attachPaginationHandlers();
                attachDeleteConfirmHandlers();
                updateSortIndicators();
            } catch (err) {
                console.error('Fetch error', err);
            }
        }

        const debouncedFetch = debounce(() => {
            state.page = 1; // resetear página al buscar
            state.search = searchInput.value.trim();
            fetchAndRender({ search: state.search, page: 1 });
        }, 350);

        // Handlers
        searchInput.addEventListener('input', debouncedFetch);

        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            state.search = '';
            state.page = 1;
            fetchAndRender({ search: '', page: 1 });
        });

        // Sorting buttons
        sortButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                const field = btn.getAttribute('data-sort');
                if (state.sort === field) {
                    state.direction = (state.direction === 'asc') ? 'desc' : 'asc';
                } else {
                    state.sort = field;
                    state.direction = 'asc';
                }
                state.page = 1;
                fetchAndRender({ sort: state.sort, direction: state.direction, page: 1 });
            });
        });

        // Pagination links handler (delegated re-attach)
        function attachPaginationHandlers() {
            const pagLinks = paginationDiv.querySelectorAll('a');
            pagLinks.forEach(a => {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url = new URL(a.href);
                    const page = url.searchParams.get('page') || 1;
                    state.page = page;
                    fetchAndRender({ page });
                });
            });
        }

        // Delete confirm handlers (SweetAlert)
        function attachDeleteConfirmHandlers() {
            const deleteButtons = document.querySelectorAll('form button[onclick="confirmDelete(this)"], button[onclick="confirmDelete(this)"]');
            deleteButtons.forEach(btn => {
                // We already attach onclick inline, but ensure duplicate protection:
                btn.removeEventListener('click', window._confirmDeleteWrapped);
                const handler = function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¡Esta acción no se puede deshacer!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = btn.closest('form');
                            if (form) form.submit();
                        }
                    });
                };
                // store to allow removal
                window._confirmDeleteWrapped = handler;
                btn.addEventListener('click', handler);
            });
        }

        // Inicial setup
        updateSortIndicators();
        attachPaginationHandlers();
        attachDeleteConfirmHandlers();

        // Handle back/forward navigation (restaurar estado)
        window.addEventListener('popstate', function () {
            const params = new URLSearchParams(window.location.search);
            state.search = params.get('search') || '';
            state.sort = params.get('sort') || '{{ $sort ?? "id" }}';
            state.direction = params.get('direction') || '{{ $direction ?? "asc" }}';
            state.page = params.get('page') || 1;
            // update inputs
            searchInput.value = state.search;
            fetchAndRender();
        });
    })();
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.getElementById('flash-success');
    if (!alert) return;

    // lee timeout desde data attribute (ms)
    const timeout = parseInt(alert.dataset.timeout || 5000, 10);

    // helper: fade out and remove
    const dismissAlert = () => {
        alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        // after transition remove from DOM
        setTimeout(() => {
            if (alert && alert.parentNode) alert.parentNode.removeChild(alert);
        }, 500);
    };

    // auto dismiss
    const timer = setTimeout(dismissAlert, timeout);

    // close button
    const closeBtn = document.getElementById('flash-success-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            clearTimeout(timer);
            dismissAlert();
        });
    }

    // if user focuses inside alert (keyboard), keep it visible — optional
    alert.addEventListener('focusin', function () {
        clearTimeout(timer);
    });
});
</script>
    @endpush
</x-layouts.app>