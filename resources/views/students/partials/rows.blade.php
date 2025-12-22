@forelse($students as $student)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->id }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->dni }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->name }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->email }}</td>

    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
        <div class="flex flex-wrap items-center justify-center gap-2">

            <!-- Ver (eye) -->
            <a href="{{ route('students.show', $student) }}"
               title="Ver {{ $student->name }}"
               aria-label="Ver {{ $student->name }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-blue-300">
                <span class="sr-only">Ver</span>
                <flux:icon name="eye" class="h-5 w-5" />
            </a>

            <!-- Editar (pencil-square) -->
            <a href="{{ route('students.edit', $student) }}"
               title="Editar {{ $student->name }}"
               aria-label="Editar {{ $student->name }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-yellow-100 dark:hover:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                <span class="sr-only">Editar</span>
                <flux:icon name="pencil-square" class="h-5 w-5" />
            </a>

             <!-- Eliminar (user-minus) - form required for DELETE -->
            <form action="{{ route('students.destroy', $student) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button"
                        onclick="confirmDelete(this)"
                        title="Eliminar {{ $student->name }}"
                        aria-label="Eliminar {{ $student->name }}"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border-0">
                    <span class="sr-only">Eliminar</span>
                    <flux:icon name="user-minus" class="h-5 w-5" />
                </button>
            </form>

            <!-- Anotar a clase (clipboard-document-check) -->
            <a href="{{ route('students.enrollClassForm', $student) }}"
               title="Anotar a clase {{ $student->name }}"
               aria-label="Anotar a clase {{ $student->name }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-green-100 dark:hover:bg-green-900 text-green-600 dark:text-green-300">
                <span class="sr-only">Anotar a clase</span>
                <flux:icon name="clipboard-document-check" class="h-5 w-5" />
            </a>

            <!-- Registrar pago (currency-dollar) -->
            @php
                $debt = isset($student->debt) ? (float)$student->debt : null;
                $paidThisMonth = !empty($student->paid_this_month);
            @endphp

            @if($paidThisMonth)
                <span title="Ya pagó este mes" class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-green-100 text-green-800" aria-label="Pagó este mes">
                    <flux:icon name="currency-dollar" class="h-5 w-5" />
                </span>
            @elseif(is_null($debt) || $debt <= 0)
                <button disabled title="Sin deuda" aria-label="Sin deuda" class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-200 dark:bg-zinc-700 text-gray-500">
                    <flux:icon name="currency-dollar" class="h-5 w-5" />
                </button>
            @else
                <a href="{{ route('payments.index', ['student_id' => $student->id]) }}"
                   title="Registrar pago de {{ $student->name }}"
                   aria-label="Registrar pago de {{ $student->name }}"
                   class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200">
                    <span class="sr-only">Registrar pago</span>
                    <flux:icon name="currency-dollar" class="h-5 w-5" />
                </a>
            @endif

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