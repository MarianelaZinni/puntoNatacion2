@forelse($students as $student)
@php
    $status       = $student->payment_status ?? 'al_dia';
    $paused       = $student->isCurrentlyPaused();
    $paidThisMonth = !empty($student->paid_this_month);
    $canPay       = !empty($student->can_pay);
@endphp
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition group">
    {{-- DNI --}}
    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $student->dni ?? '—' }}</td>

    {{-- Name --}}
    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">
        <a href="{{ route('students.show', $student) }}" class="hover:text-[#29b1dc] transition">{{ $student->name }}</a>
    </td>

    {{-- Email (hidden on small) --}}
    <td class="hidden md:table-cell px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $student->email ?? '—' }}</td>

    {{-- Status --}}
    <td class="px-4 py-3 text-center">
        @if($paused)
            <span title="Pausado" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                <flux:icon name="pause-circle" class="h-3.5 w-3.5" />
                <span class="hidden sm:inline">Pausado</span>
            </span>
        @elseif($status === 'deudor')
            <span title="Deudor" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                <flux:icon name="x-circle" class="h-3.5 w-3.5" />
                <span class="hidden sm:inline">Deudor</span>
            </span>
        @elseif($status === 'pendiente')
            <span title="Pago pendiente" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                <flux:icon name="exclamation-circle" class="h-3.5 w-3.5" />
                <span class="hidden sm:inline">Pendiente</span>
            </span>
        @else
            <span title="Al día" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                <flux:icon name="check-circle" class="h-3.5 w-3.5" />
                <span class="hidden sm:inline">Al día</span>
            </span>
        @endif
    </td>

    {{-- Actions --}}
    <td class="px-4 py-3">
        <div class="flex items-center justify-end gap-1">
            {{-- Ver --}}
            <a href="{{ route('students.show', $student) }}"
               title="Ver alumno"
               class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 transition">
                <flux:icon name="eye" class="h-4 w-4" />
                <span class="sr-only">Ver</span>
            </a>

            {{-- @if(auth()->user()->role === 'enfermeria') --}}
            {{-- Revisión médica (acceso directo para enfermería) --}}
            {{-- <a href="{{ route('medical_checkups.index', ['student_id' => $student->id]) }}" ... </a> --}}
            {{-- @else --}}
            {{-- Editar --}}
            <a href="{{ route('students.edit', $student) }}"
               title="Editar alumno"
               class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 hover:text-yellow-600 dark:hover:text-yellow-400 transition">
                <flux:icon name="pencil-square" class="h-4 w-4" />
                <span class="sr-only">Editar</span>
            </a>

            {{-- Historial de pagos --}}
            <a href="{{ route('payments.history', ['student_id' => $student->id]) }}"
               title="Historial de pagos"
               class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <flux:icon name="clock" class="h-4 w-4" />
                <span class="sr-only">Historial</span>
            </a>

            {{-- Más acciones dropdown --}}
            <div class="relative" data-actions-dropdown>
                <button type="button"
                        data-dropdown-toggle
                        aria-haspopup="true"
                        aria-expanded="false"
                        title="Más acciones"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-[#29b1dc]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                    </svg>
                    <span class="sr-only">Más acciones</span>
                </button>

                <div data-dropdown-menu
                     class="hidden absolute right-0 z-20 mt-1 w-52 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-1"
                     role="menu">

                    {{-- Anotar a clase --}}
                    <a href="{{ route('students.enrollClassForm', $student) }}"
                       role="menuitem"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/40 hover:text-green-700 dark:hover:text-green-300">
                        <flux:icon name="clipboard-document-check" class="h-4 w-4 shrink-0" />
                        Anotar a clase
                    </a>

                    {{-- Registrar pago --}}
                    @if($canPay)
                        <a href="{{ route('payments.index', ['student_id' => $student->id]) }}"
                           role="menuitem"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <flux:icon name="currency-dollar" class="h-4 w-4 shrink-0" />
                            Registrar pago
                        </a>
                    @else
                        <span role="menuitem"
                              class="flex items-center gap-2 px-4 py-2 text-sm text-gray-400 dark:text-gray-500 cursor-default"
                              title="{{ $paidThisMonth ? 'Ya pagó este mes' : 'Sin deuda' }}">
                            <flux:icon name="currency-dollar" class="h-4 w-4 shrink-0" />
                            Registrar pago
                            @if($paidThisMonth)<span class="ml-auto text-xs text-green-600 dark:text-green-400">✓</span>@endif
                        </span>
                    @endif

                    {{-- Revisión Médica --}}
                    <a href="{{ route('medical_checkups.index', ['student_id' => $student->id]) }}"
                       role="menuitem"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-lime-50 dark:hover:bg-lime-900/40 hover:text-lime-700 dark:hover:text-lime-300">
                        <flux:icon name="heart" class="h-4 w-4 shrink-0" />
                        Revisión médica
                    </a>

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
            {{-- @endif --}}
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
        <div class="flex flex-col items-center gap-2">
            <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>No hay alumnos registrados.</span>
        </div>
    </td>
</tr>
@endforelse