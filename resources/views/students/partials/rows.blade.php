@forelse($students as $student)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->id }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->dni }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->name }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->email }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
        <div class="flex flex-wrap items-center justify-center gap-2">
            <a href="{{ route('students.edit', $student) }}"
               class="inline-flex items-center px-2 py-1 bg-yellow-500 dark:bg-yellow-700 text-white rounded text-xs hover:bg-yellow-600 dark:hover:bg-yellow-800 transition">
                Editar
            </a>

            <a href="{{ route('students.show', $student) }}"
               class="inline-flex items-center px-2 py-1 bg-blue-500 dark:bg-blue-700 text-white rounded text-xs hover:bg-blue-600 dark:hover:bg-blue-800 transition">
                Ver
            </a>

            <a href="{{ route('students.enrollClassForm', $student) }}"
               class="inline-flex items-center px-2 py-1 bg-green-600 dark:bg-green-800 text-white rounded text-xs hover:bg-green-700 dark:hover:bg-green-900 transition">
                Anotar a clase
            </a>

            <a href="{{ route('students.enrollClassForm', $student) }}"
               class="inline-flex items-center px-2 py-1 bg-gray-800 dark:bg-gray-900 text-white rounded text-xs hover:bg-gray-900 dark:hover:bg-black transition">
                Registrar pago
            </a>

            <form action="{{ route('students.destroy', $student) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button"
                    onclick="confirmDelete(this)"
                    class="inline-flex items-center px-2 py-1 bg-red-600 dark:bg-red-800 text-white rounded text-xs hover:bg-red-700 dark:hover:bg-red-900 transition">
                    Eliminar
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="px-4 py-6 text-center text-gray-600 dark:text-gray-400">
        No hay alumnos registrados.
    </td>
</tr>
@endforelse