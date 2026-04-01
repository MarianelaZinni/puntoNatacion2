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
        $paused = $student->isCurrentlyPaused();
    @endphp
    <td class="px-4 py-3 text-center">
        @if($paused)
            <span title="Pausado" class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-amber-100 text-amber-700" aria-label="Pausado">
                <flux:icon name="pause-circle" class="h-5 w-5" />
            </span>
        @elseif($status === 'deudor')
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
        @php
            $debt = isset($student->debt) ? (float)$student->debt : 0.0;
            $paidThisMonth = !empty($student->paid_this_month);
            $hasUnpaid = !empty($student->unpaid_periods) && is_array($student->unpaid_periods) && count($student->unpaid_periods) > 0;
        @endphp

        {{-- Dropdown de acciones --}}
        <div class="relative flex justify-center" data-actions-dropdown>
            <button type="button"
                    data-dropdown-toggle
                    aria-haspopup="true"
                    aria-expanded="false"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                    title="Acciones para {{ $student->name }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
                <span class="sr-only">Acciones</span>
            </button>

            {{-- Menú desplegable --}}
            <div data-dropdown-menu
                 class="hidden absolute right-0 z-20 mt-10 w-52 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-1"
                 role="menu">

                {{-- Ver --}}
                <a href="{{ route('students.show', $student) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/40 hover:text-blue-700 dark:hover:text-blue-300">
                    <flux:icon name="eye" class="h-4 w-4 shrink-0" />
                    Ver alumno
                </a>

                {{-- Editar --}}
                <a href="{{ route('students.edit', $student) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-yellow-50 dark:hover:bg-yellow-900/40 hover:text-yellow-700 dark:hover:text-yellow-300">
                    <flux:icon name="pencil-square" class="h-4 w-4 shrink-0" />
                    Editar
                </a>

                {{-- Anotar a clase --}}
                <a href="{{ route('students.enrollClassForm', $student) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/40 hover:text-green-700 dark:hover:text-green-300">
                    <flux:icon name="clipboard-document-check" class="h-4 w-4 shrink-0" />
                    Anotar a clase
                </a>

                {{-- Registrar pago --}}
                @if($paidThisMonth && !$hasUnpaid)
                    <span role="menuitem"
                          class="flex items-center gap-2 px-4 py-2 text-sm text-gray-400 dark:text-gray-500 cursor-default"
                          title="Ya pagó este mes">
                        <flux:icon name="currency-dollar" class="h-4 w-4 shrink-0" />
                        Registrar pago <span class="ml-auto text-xs text-green-600 dark:text-green-400">✓</span>
                    </span>
                 @elseif($debt <= 0 && !$hasUnpaid && $status !== 'pendiente')
                    <span role="menuitem"
                          class="flex items-center gap-2 px-4 py-2 text-sm text-gray-400 dark:text-gray-500 cursor-default"
                          title="Sin deuda">
                        <flux:icon name="currency-dollar" class="h-4 w-4 shrink-0" />
                        Registrar pago
                    </span>
                @else
                    <a href="{{ route('payments.index', ['student_id' => $student->id]) }}"
                       role="menuitem"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <flux:icon name="currency-dollar" class="h-4 w-4 shrink-0" />
                        Registrar pago
                    </a>
                @endif

                {{-- Historial de pagos --}}
                <a href="{{ route('payments.history', ['student_id' => $student->id]) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-300">
                    <flux:icon name="clock" class="h-4 w-4 shrink-0" />
                    Historial de pagos
                </a>

                {{-- Revisión Médica --}}
                <a href="{{ route('medical_checkups.index', ['student_id' => $student->id]) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-lime-50 dark:hover:bg-lime-900/40 hover:text-lime-700 dark:hover:text-lime-300">
                    <flux:icon name="heart" class="h-4 w-4 shrink-0" />
                    Revisión médica
                </a>

                {{-- Períodos de pausa 
                <a href="{{ route('students.pauses.index', $student) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-amber-50 dark:hover:bg-amber-900/40 hover:text-amber-700 dark:hover:text-amber-300">
                    <flux:icon name="pause-circle" class="h-4 w-4 shrink-0" />
                    Períodos de pausa
                </a>--}}

                <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                {{-- Eliminar --}}
                <form action="{{ route('students.destroy', $student) }}" method="POST" class="block">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            onclick="confirmDelete(this)"
                            role="menuitem"
                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40">
                        <flux:icon name="user-minus" class="h-4 w-4 shrink-0" />
                        Eliminar alumno
                    </button>
                </form>
            </div>
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