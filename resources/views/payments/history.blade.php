<x-layouts.app title="Historial de pagos">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Historial de pagos</h1>

            <form method="GET" action="{{ route('payments.history') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <label for="student_id" class="sr-only">Filtrar por alumno</label>
                <select name="student_id" id="student_id" class="w-full sm:w-auto rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2">
                    <option value="">— Todos los alumnos —</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ (string)($studentId ?? '') === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->name }} @if($s->dni) ({{ $s->dni }}) @endif
                        </option>
                    @endforeach
                </select>

                {{-- Period filter: desde / hasta (type="month") --}}
                <label for="period_from" class="sr-only">Periodo desde</label>
                <input type="month" id="period_from" name="period_from" value="{{ request('period_from') ?? '' }}"
                       class="w-full sm:w-auto rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2"
                       title="Periodo desde (YYYY-MM)">

                <label for="period_to" class="sr-only">Periodo hasta</label>
                <input type="month" id="period_to" name="period_to" value="{{ request('period_to') ?? '' }}"
                       class="w-full sm:w-auto rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-2"
                       title="Periodo hasta (YYYY-MM)">

                <button type="submit" class="w-full sm:w-auto px-3 py-2 rounded bg-[#29b1dc] text-white hover:bg-[#24a8cf]">Filtrar</button>

                @if(!empty($studentId) || request()->hasAny(['period_from','period_to']))
                    <a href="{{ route('payments.history') }}" class="text-sm text-gray-600 dark:text-gray-300 underline">Quitar filtro</a>
                @endif
            </form>
        </div>

        {{-- Banner indicador de deuda para el alumno seleccionado --}}
        @if(!empty($selectedStudent) && is_array($debtSummary))
            @php
                $debtAmount = $debtSummary['debt'] ?? 0.0;
                $monthlyAmount = $debtSummary['monthly_amount'] ?? 0.0;
                $nextPeriod = $debtSummary['next_unpaid_period'] ?? null;
            @endphp

            <div class="mb-4">
                @if($debtAmount > 0)
                    <div class="p-3 rounded border border-red-200 bg-red-50 text-red-800 flex items-center justify-between">
                        <div>
                            <strong>El alumno posee deuda:</strong>
                            <span class="ml-2 font-semibold">${{ number_format($debtAmount, 2, ',', '.') }}</span>
                            @if($nextPeriod)
                                <span class="ml-3 text-sm">Próximo periodo impago: <span class="font-medium">{{ $nextPeriod }}</span></span>
                            @endif
                        </div>
                        <div class="text-sm text-red-800">Alumno: <span class="font-medium">{{ $selectedStudent->name }}</span></div>
                    </div>
                @else
                    <div class="p-3 rounded border border-green-200 bg-green-50 text-green-800 flex items-center justify-between">
                        <div>
                            <strong>No posee deuda:</strong>
                            <span class="ml-2 text-sm">Total abonado: <span class="font-semibold">${{ number_format($debtSummary['total_paid'] ?? 0, 2, ',', '.') }}</span></span>
                        </div>
                        <div class="text-sm text-green-800">Alumno: <span class="font-medium">{{ $selectedStudent->name }}</span></div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Mensajes flash --}}
        @if(session('success'))
            <div id="flash-success" class="mb-4 p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/30 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-3 shadow-sm"
                 role="status" aria-live="polite" data-timeout="5000">
                <div class="flex-1 text-base leading-relaxed">
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

        @if(session('error'))
            <div id="flash-error" class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200 flex items-start gap-3 shadow-sm"
                 role="alert" aria-live="assertive" data-timeout="8000">
                <div class="flex-1 text-base leading-relaxed">
                    {{ session('error') }}
                </div>
                <button type="button"
                        class="ml-2 -mr-1 p-1 rounded hover:bg-red-100 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-red-800 dark:text-red-200"
                        aria-label="Cerrar mensaje" id="flash-error-close">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-3 sm:p-6 shadow-sm">
            @if($payments->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                    No se encontraron pagos.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse table-auto">
                        <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Alumno</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Fecha</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Periodo</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Tipo</th>                          <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Método</th>
                            <th class="text-right text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Monto (AR$)</th>
                            <th class="text-left text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Notas</th>
                            <th class="text-center text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($payments as $payment)
                            @php
                                // Resolvemos el periodo a mostrar: preferimos payment_period, si no existe usamos payment_date.
                                $periodSource = $payment->payment_period ?? $payment->payment_date;
                                try {
                                    $periodLabel = $periodSource ? \Carbon\Carbon::parse($periodSource)->format('m/Y') : '-';
                                } catch (\Throwable $e) {
                                    $periodLabel = '-';
                                }
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->student->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}
                                </td>

                                {{-- Columna: Periodo --}}
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $periodLabel }}
                                </td>

                                 {{-- Columna: Tipo de pago --}}
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    @php
                                        $typeLabels = \App\Models\Payment::paymentTypes();
                                        $typeLabel  = $typeLabels[$payment->payment_type ?? 'normal'] ?? 'Pago normal';
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                        @if(($payment->payment_type ?? 'normal') === 'medio_mes') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                        @elseif(($payment->payment_type ?? 'normal') === 'con_recargo') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                        @else bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                        @endif">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->paymentMethod->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-300">
                                    {{ number_format($payment->amount, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $payment->notes ?? '-' }}
                                </td>
                                
                                {{-- Columna: Acciones --}}
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Botón Editar --}}
                                        <a href="{{ route('payments.edit', $payment) }}"
                                                       class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-yellow-100 dark:hover:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                <span class="sr-only">Editar</span>
                <flux:icon name="pencil-square" class="h-5 w-5" />
                                                    </a>
                                        
                                        {{-- Formulario Eliminar --}}
                                        <form action="{{ route('payments.destroy', $payment) }}" 
                                              method="POST" 
                                              class="inline payment-delete-form"
                                              data-payment-id="{{ $payment->id }}"
                                              data-payment-amount="{{ number_format($payment->amount, 2, ',', '.') }}"
                                              data-payment-period="{{ $periodLabel }}"
                                              data-student-name="{{ $payment->student->name ?? '' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                                onclick="confirmDeletePayment(this)"
                                                                 class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border-0"
                                                                title="Eliminar pago">
                                                            <span class="sr-only">Eliminar</span>
                                                        <flux:icon name="x-circle" class="h-5 w-5" />
                                                        </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if(method_exists($payments, 'links'))
                    <div class="mt-4">
                        {{ $payments->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
    
    @push('scripts')
    <!-- SweetAlert2 (CDN) -->
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Confirm delete payment with SweetAlert2
        window.confirmDeletePayment = function (btn) {
            const form = btn.closest('form');
            if (!form) return;

            const paymentAmount = form.dataset.paymentAmount || '';
            const paymentPeriod = form.dataset.paymentPeriod || '';
            const studentName = form.dataset.studentName || '';

            Swal.fire({
                    title: '¿Eliminar este pago?',
                    html: `<div class="text-left">
                        ${studentName ? `<p class="mb-2">Alumno: <strong>${studentName}</strong></p>` : ''}
                        <p class="mb-2">Periodo: <strong>${paymentPeriod}</strong></p>
                        <p>Monto: <strong>$${paymentAmount}</strong></p>
                        <p class="mt-3 text-sm text-gray-600">Esta acción no se puede deshacer.</p>
                    </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    focusCancel: true,
                    customClass: {
                        popup: 'text-left'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
        };
        
        // Flash message auto-dismiss
        function initFlash(id, closeId) {
            const el = document.getElementById(id);
            if (!el) return;

            const timeout = parseInt(el.dataset.timeout || 5000, 10);

            const dismiss = () => {
                el.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => {
                    if (el && el.parentNode) el.parentNode.removeChild(el);
                }, 500);
            };

            const timer = setTimeout(dismiss, timeout);

            const closeBtn = document.getElementById(closeId);
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    clearTimeout(timer);
                    dismiss();
                });
            }
        }

        initFlash('flash-success', 'flash-success-close');
        initFlash('flash-error', 'flash-error-close');
    });
    </script>
    @endpush
</x-layouts.app>