@forelse($teachers as $teacher)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->id }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->dni ?? '—' }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->name }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->email ?? '—' }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->phone ?? '—' }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
        {{-- Dropdown de acciones --}}
        <div class="relative flex justify-center" data-actions-dropdown>
            <button type="button"
                    data-dropdown-toggle
                    aria-haspopup="true"
                    aria-expanded="false"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                    title="Acciones para {{ $teacher->name }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
                <span class="sr-only">Acciones</span>
            </button>

            {{-- Menú desplegable --}}
            <div data-dropdown-menu
                 class="hidden absolute right-0 z-20 mt-10 w-44 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-1"
                 role="menu">

                {{-- Ver --}}
                <a href="{{ route('teachers.show', $teacher) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-700 dark:hover:text-blue-300">
                    <flux:icon name="eye" class="h-4 w-4 shrink-0" />
                    Ver profesor
                </a>

                {{-- Editar --}}
                <a href="{{ route('teachers.edit', $teacher) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-yellow-50 dark:hover:bg-yellow-900/40 hover:text-yellow-700 dark:hover:text-yellow-300">
                    <flux:icon name="pencil-square" class="h-4 w-4 shrink-0" />
                    Editar
                </a>

                <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                {{-- Eliminar --}}
                <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" class="block">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            onclick="confirmDelete(this)"
                            role="menuitem"
                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40">
                        <flux:icon name="user-minus" class="h-4 w-4 shrink-0" />
                        Eliminar profesor
                    </button>
                </form>
            </div>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="px-4 py-6 text-center text-gray-600 dark:text-gray-400">
        No hay profesores registrados.
    </td>
</tr>
@endforelse
