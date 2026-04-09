@forelse($students as $student)
@php
    $status = $student->payment_status ?? 'al_dia';
    $paused = $student->isCurrentlyPaused();
    $debt = isset($student->debt) ? (float)$student->debt : 0.0;
    $paidThisMonth = !empty($student->paid_this_month);
    $hasUnpaid = !empty($student->unpaid_periods) && is_array($student->unpaid_periods) && count($student->unpaid_periods) > 0;
    $canPay = !($paidThisMonth && !$hasUnpaid) && !($debt <= 0 && !$hasUnpaid && $status !== 'pendiente');
@endphp
<div class="px-4 py-4 flex items-start gap-3 bg-white dark:bg-gray-900">

    {{-- Status indicator --}}
    <div class="mt-0.5 shrink-0">
        @if($paused)
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300" title="Pausado">
                <flux:icon name="pause-circle" class="h-5 w-5" />
            </span>
        @elseif($status === 'deudor')
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300" title="Deudor">
                <flux:icon name="x-circle" class="h-5 w-5" />
            </span>
        @elseif($status === 'pendiente')
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300" title="Pago pendiente">
                <flux:icon name="exclamation-circle" class="h-5 w-5" />
            </span>
        @else
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300" title="Al día">
                <flux:icon name="check-circle" class="h-5 w-5" />
            </span>
        @endif
    </div>

    {{-- Info --}}
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $student->name }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $student->dni ?? '—' }}</p>
        @if($student->email)
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $student->email }}</p>
        @endif
    </div>

    {{-- Action buttons --}}
    <div class="flex items-center gap-1 shrink-0">
        {{-- Ver --}}
        <a href="{{ route('students.show', $student) }}"
           title="Ver alumno"
           class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 transition">
            <flux:icon name="eye" class="h-5 w-5" />
        </a>

        {{-- Editar --}}
        <a href="{{ route('students.edit', $student) }}"
           title="Editar alumno"
           class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 hover:text-yellow-600 dark:hover:text-yellow-400 transition">
            <flux:icon name="pencil-square" class="h-5 w-5" />
        </a>

        {{-- Más acciones --}}
        <div class="relative" data-actions-dropdown>
            <button type="button"
                    data-dropdown-toggle
                    aria-haspopup="true"
                    aria-expanded="false"
                    title="Más acciones"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition focus:outline-none focus:ring-2 focus:ring-[#29b1dc]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
                <span class="sr-only">Más acciones</span>
            </button>

            <div data-dropdown-menu
                 class="hidden absolute right-0 z-20 mt-1 w-56 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-1"
                 role="menu">

                <a href="{{ route('students.enrollClassForm', $student) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/40 hover:text-green-700 dark:hover:text-green-300">
                    <flux:icon name="clipboard-document-check" class="h-4 w-4 shrink-0" />
                    Anotar a clase
                </a>

                @if($canPay)
                    <a href="{{ route('payments.index', ['student_id' => $student->id]) }}"
                       role="menuitem"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <flux:icon name="currency-dollar" class="h-4 w-4 shrink-0" />
                        Registrar pago
                    </a>
                @else
                    <span role="menuitem"
                          class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-400 dark:text-gray-500 cursor-default">
                        <flux:icon name="currency-dollar" class="h-4 w-4 shrink-0" />
                        Registrar pago
                        @if($paidThisMonth)<span class="ml-auto text-xs text-green-600 dark:text-green-400">✓</span>@endif
                    </span>
                @endif

                <a href="{{ route('payments.history', ['student_id' => $student->id]) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-300">
                    <flux:icon name="clock" class="h-4 w-4 shrink-0" />
                    Historial de pagos
                </a>

                <a href="{{ route('medical_checkups.index', ['student_id' => $student->id]) }}"
                   role="menuitem"
                   class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-lime-50 dark:hover:bg-lime-900/40 hover:text-lime-700 dark:hover:text-lime-300">
                    <flux:icon name="heart" class="h-4 w-4 shrink-0" />
                    Revisión médica
                </a>

                <div class="my-1 border-t border-gray-100 dark:border-gray-800"></div>

                <form action="{{ route('students.destroy', $student) }}" method="POST" class="block">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            onclick="confirmDelete(this)"
                            role="menuitem"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40">
                        <flux:icon name="user-minus" class="h-4 w-4 shrink-0" />
                        Eliminar alumno
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@empty
<div class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
    <div class="flex flex-col items-center gap-2">
        <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span>No hay alumnos registrados.</span>
    </div>
</div>
@endforelse
