@forelse($students as $student)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->id }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->dni }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->name }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $student->email }}</td>

    <!-- Estado column -->
    @php
        // payment_status values: 'deudor', 'pendiente', 'al_dia'
        $status = $student->payment_status ?? 'al_dia';
    @endphp
    <td class="px-4 py-3 text-center">
        @if($status === 'deudor')
            <span title="Deudor" class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-red-100 text-red-700" aria-label="Deudor">
                <flux:icon name="x-circle" class="h-5 w-5" />
            </span>
        @elseif($status === 'pendiente')
            <span title="Pago pendiente" class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-amber-100 text-amber-700" aria-label="Pago pendiente">
                <flux:icon name="exclamation-circle" class="h-5 w-5" />
            </span>
        @else
            <span title="Al día" class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-green-100 text-green-700" aria-label="Al día">
                <flux:icon name="check-circle" class="h-5 w-5" />
            </span>
        @endif
    </td>

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
                $debt = isset($student->debt) ? (float)$student->debt : 0.0;
                $paidThisMonth = !empty($student->paid_this_month);
                // comprobar si tiene periodos impagos
                $hasUnpaid = !empty($student->unpaid_periods) && is_array($student->unpaid_periods) && count($student->unpaid_periods) > 0;
            @endphp

            @if($paidThisMonth && !$hasUnpaid)
                {{-- Si pagó este mes y NO tiene periodos impagos, inhabilitar --}}
                <span title="Ya pagó este mes" class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-green-100 text-green-800" aria-label="Pagó este mes">
                    <flux:icon name="currency-dollar" class="h-5 w-5" />
                </span>
            @elseif($debt <= 0 && !$hasUnpaid)
                {{-- Sin deuda y sin periodos impagos --}}
                <button disabled title="Sin deuda" aria-label="Sin deuda" class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-200 dark:bg-zinc-700 text-gray-500">
                    <flux:icon name="currency-dollar" class="h-5 w-5" />
                </button>
            @else
                {{-- Si tiene deuda total o periodos impagos, permitimos registrar pago --}}
                <a href="{{ route('payments.index', ['student_id' => $student->id]) }}"
                   title="Registrar pago de {{ $student->name }}"
                   aria-label="Registrar pago de {{ $student->name }}"
                   class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200">
                    <span class="sr-only">Registrar pago</span>
                    <flux:icon name="currency-dollar" class="h-5 w-5" />
                </a>
            @endif

            <!-- Historial de pagos (new action) -->
            <a href="{{ route('payments.history', ['student_id' => $student->id]) }}"
               title="Historial de pagos de {{ $student->name }}"
               aria-label="Historial de pagos de {{ $student->name }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-600 dark:text-indigo-300">
                <span class="sr-only">Historial de pagos</span>
                <flux:icon name="clock" class="h-5 w-5" />
            </a>

            {{-- Botón Revisión Médica (NUEVO) --}}
                        <a href="{{ route('medical_checkups.index', ['student_id' => $student->id]) }}"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-lime-100 dark:hover:bg-lime-900 text-lime-600 dark:text-lime-300">
                <span class="sr-only">Revisión médica</span>
                <flux:icon name="heart" class="h-5 w-5" />
                        </a>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="px-4 py-6 text-center text-gray-600 dark:text-gray-400">
        No hay alumnos registrados.
    </td>
</tr>
@endforelse