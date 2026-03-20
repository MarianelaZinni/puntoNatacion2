@forelse($teachers as $teacher)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->id }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->dni ?? '—' }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->name }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->email ?? '—' }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $teacher->phone ?? '—' }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
        <div class="flex flex-wrap items-center justify-center gap-2">

            <!-- Ver -->
            <a href="{{ route('teachers.show', $teacher) }}"
               title="Ver {{ $teacher->name }}"
               aria-label="Ver {{ $teacher->name }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-blue-300">
                <span class="sr-only">Ver</span>
                <flux:icon name="eye" class="h-5 w-5" />
            </a>

            <!-- Editar -->
            <a href="{{ route('teachers.edit', $teacher) }}"
               title="Editar {{ $teacher->name }}"
               aria-label="Editar {{ $teacher->name }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-yellow-100 dark:hover:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                <span class="sr-only">Editar</span>
                <flux:icon name="pencil-square" class="h-5 w-5" />
            </a>

            <!-- Eliminar -->
            <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button"
                        onclick="confirmDelete(this)"
                        title="Eliminar {{ $teacher->name }}"
                        aria-label="Eliminar {{ $teacher->name }}"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border-0">
                    <span class="sr-only">Eliminar</span>
                    <flux:icon name="user-minus" class="h-5 w-5" />
                </button>
            </form>
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
