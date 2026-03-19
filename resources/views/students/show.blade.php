<x-layouts.app title="Ver alumno">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Datos del Alumno</h1>
        </div>

        {{-- Flash success (auto-dismiss + close) --}}
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

        {{-- Flash error (auto-dismiss + close) --}}
        @if(session('error'))
            <div id="flash-error"
                 class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200 flex items-start gap-3 shadow-sm"
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

        {{-- Detalles --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-8 gap-x-6 text-base">
                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">ID</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->id }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">DNI / Documento</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->dni ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Nombre</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->name }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Email</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->email ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Teléfono</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->phone ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Fecha de Nacimiento</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">
                        {{ $student->birth_date ? $student->birth_date->format('d/m/Y') : '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Edad</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">
                        @if($student->birth_date && isset($student->age))
                            {{ $student->age }} {{ $student->age === 1 ? 'año' : 'años' }}
                        @else
                            -
                        @endif
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Dirección</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->address ?? '-' }}</dd>
                </div>
<div class="sm:col-span-2">
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Observaciones</dt>
                    <dd class="mt-3 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $student->observations ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Creado</dt>
                    <dd class="mt-3 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">{{ $student->created_at ? $student->created_at->diffForHumans() . ' — ' . $student->created_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Última actualización</dt>
                    <dd class="mt-3 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">{{ $student->updated_at ? $student->updated_at->diffForHumans() . ' — ' . $student->updated_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>
            </dl>

            {{-- COSTO ESTIMADO --}}
            <div class="mt-6">
                <div class="bg-gray-50 dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 rounded p-4 inline-block">
                    <div class="text-lg font-bold text-gray-700 dark:text-gray-200">Cuota mensual</div>
                    <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mt-2">
                        @php
                            $total = $priceSummary['total'] ?? null;
                        @endphp
                        {{ $total !== null ? number_format($total, 0, ',', '.') : '—' }}
                    </div>

                    @if(!empty($priceSummary['details']))
                        <div class="mt-2 text-xs text-red-600 dark:text-red-300">
                            @foreach($priceSummary['details'] as $d)
                                <div>{{ $d }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Clases inscritas --}}
            <div class="mt-8">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Clases inscritas</h2>

                @if($student->subjects->isEmpty())
                    <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                        El alumno no está inscripto en ninguna clase.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="text-xs text-gray-500 uppercase">
                                    <th class="px-3 py-2">Materia</th>
                                    <th class="px-3 py-2">Día</th>
                                    <th class="px-3 py-2">Horario</th>
                                    <th class="px-3 py-2">Cupo</th>
                                    <th class="px-3 py-2">Inscriptos</th>
                                    <th class="px-3 py-2">Libre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->subjects as $subject)
                                    @php
                                        $subjectType = $subject->subjectType;
                                        $title = $subjectType->description ?? $subjectType->value ?? 'Sin materia';
                                        $enrolled = $subject->students_count ?? ($subject->students ? $subject->students->count() : 0);
                                        $free = max(0, ($subject->capacity ?? 0) - $enrolled);
                                    @endphp
                                    <tr class="border-t">
                                        <td class="px-3 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $title }}</td>
                                        <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ $subject->day }}</td>
                                        <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ ($subject->start_time ?? '') . ' - ' . ($subject->end_time ?? '') }}</td>
                                        <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ $subject->capacity ?? '-' }}</td>
                                        <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ $enrolled }}</td>
                                        <td class="px-3 py-3 text-gray-600 dark:text-gray-200">{{ $free }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- HISTORIAL DE PAGOS --}}
            <div class="mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Historial de pagos</h2>

                    <div class="flex items-center gap-3">
                        {{-- Cálculo de deuda y periodos impagos usando helper del modelo --}}
                        @php
                            // Intentamos usar el helper del modelo; si no existe, fall back a valores simples.
                            if (method_exists($student, 'calculateDebtFromCreationUsingCurrentMonthly')) {
                                $debtSummary = $student->calculateDebtFromCreationUsingCurrentMonthly();
                            } else {
                                $debtSummary = [
                                    'debt' => 0.0,
                                    'monthly_amount' => $priceSummary['total'] ?? 0.0,
                                    'unpaid_periods' => [],
                                    'selectable_periods' => [],
                                    'next_unpaid_period' => null,
                                ];
                            }

                            $debtAmount = $debtSummary['debt'] ?? 0.0;
                            $unpaidPeriods = $debtSummary['unpaid_periods'] ?? [];
                            $selectable = $debtSummary['selectable_periods'] ?? [];
                            $nextUnpaid = $debtSummary['next_unpaid_period'] ?? null;

                            // lógica para habilitar el botón Registrar pago:
                            // habilitar si tiene deuda (debtAmount > 0) o si existen selectable periods (por ej. pagar mes actual)
                            $canRegisterPayment = ($debtAmount > 0) || (!empty($selectable) && count($selectable) > 0);
                        @endphp

                        {{-- Botón para registrar pago preseleccionando el alumno --}}
                        <a href="{{ route('payments.index', ['student_id' => $student->id]) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none"
                           @unless($canRegisterPayment) aria-disabled="true" onclick="event.preventDefault();" style="opacity:0.6;pointer-events:none;" @endunless>
                            <flux:icon name="currency-dollar" class="h-4 w-4" />
                            Registrar pago
                        </a>

                        <a href="{{ route('payments.history', ['search' => $student->name]) }}" class="text-sm text-gray-600 dark:text-gray-300 underline">
                            Ver historial completo
                        </a>
                    </div>
                </div>

                {{-- Banner de deuda --}}
                <div class="mb-4">
                    @if($debtAmount > 0)
                        <div class="p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 text-red-800 dark:text-red-200 flex items-start gap-4">
                            <div class="flex-1">
                                <div class="text-sm">
                                    <strong>El alumno posee deuda:</strong>
                                    <span class="ml-2 font-semibold">${{ number_format($debtAmount, 2, ',', '.') }}</span>
                                </div>
                                @if($nextUnpaid)
                                    <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                        Próximo periodo impago: <span class="font-medium">{{ $nextUnpaid }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-sm font-medium text-red-800 dark:text-red-200">Adeuda</div>
                        </div>
                    @else
                        <div class="p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-4">
                            <div class="flex-1">
                                <div class="text-sm">
                                    <strong>No posee deuda:</strong>
                                    <span class="ml-2 text-sm">Total abonado: <span class="font-semibold">${{ number_format($student->total_paid ?? 0, 2, ',', '.') }}</span></span>
                                </div>
                            </div>
                            <div class="text-sm font-medium text-green-800 dark:text-green-200">Al día</div>
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-4">
                    <div class="mb-3 text-sm text-gray-700 dark:text-gray-200 flex items-center justify-between">
                        <div>
                            Total abonado: <span class="font-semibold">${{ number_format($student->total_paid ?? 0, 2, ',', '.') }}</span>
                        </div>
                        @if($student->paid_this_month)
                            <div class="text-sm text-green-700 dark:text-green-200 font-medium">Pagó este mes</div>
                        @endif
                    </div>

                    {{-- Mostrar listados de periodos impagos (detalle) --}}
                    @if(!empty($unpaidPeriods))
                        <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-100 dark:border-yellow-800 rounded text-sm text-yellow-800 dark:text-yellow-200">
                            <div class="font-medium mb-2">Periodos adeudados</div>
                            <ul class="list-inside list-disc space-y-1">
                                @foreach($unpaidPeriods as $up)
                                    <li>
                                        {{ $up['period'] ?? '-' }} — Pagado: ${{ number_format($up['paid'] ?? 0, 2, ',', '.') }} — Falta: <span class="font-semibold">${{ number_format($up['deficit'] ?? 0, 2, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($student->payments->isEmpty())
                        <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                            No se encontraron pagos para este alumno.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs text-gray-500 uppercase">
                                        <th class="px-3 py-2 text-left">Fecha</th>
                                        <th class="px-3 py-2 text-left">Periodo</th>
                                        <th class="px-3 py-2 text-left">Método</th>
                                        <th class="px-3 py-2 text-right">Monto (AR$)</th>
                                        <th class="px-3 py-2 text-left">Notas</th>
                                        <th class="px-3 py-2 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->payments as $payment)
                                        @php
                                            // Resolvemos el periodo a mostrar: preferimos payment_period, si no existe usamos payment_date.
                                            $periodSource = $payment->payment_period ?? $payment->payment_date;
                                            try {
                                                $periodLabel = $periodSource ? \Carbon\Carbon::parse($periodSource)->format('m/Y') : '-';
                                            } catch (\Throwable $e) {
                                                $periodLabel = '-';
                                            }
                                        @endphp
                                        <tr class="border-t hover:bg-gray-50 dark:hover:bg-zinc-800">
                                            <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-200">
                                                {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}
                                            </td>

                                            {{-- Columna: Periodo --}}
                                            <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-200">
                                                {{ $periodLabel }}
                                            </td>

                                            <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-200">
                                                {{ $payment->paymentMethod->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-3 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                                                {{ number_format($payment->amount, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-200">
                                                {{ $payment->notes ?? '-' }}
                                            </td>
                                            
                                            {{-- Columna: Acciones (NUEVA) --}}
                                            <td class="px-3 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
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
                                                          data-payment-period="{{ $periodLabel }}">
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
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-6 pt-3 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('students.index') }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition text-base">
                    Volver
                </a>

                <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition text-base">
                    Editar
                </a>

                <form action="{{ route('students.destroy', $student) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            onclick="confirmDeleteStudent(this)"
                            title="Eliminar {{ $student->name }}"
                            aria-label="Eliminar {{ $student->name }}"
                            class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition text-base">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- SweetAlert2 (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Confirm delete student with SweetAlert2
        window.confirmDeleteStudent = function (btn) {
            const form = btn.closest('form');
            if (!form) return;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Se eliminará el alumno y todos sus datos asociados. Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                // Fallback to native confirm
                if (confirm('¿Seguro que querés eliminar este alumno? Esta acción no se puede deshacer.')) {
                    form.submit();
                }
            }
        };

        // Confirm delete payment with SweetAlert2
        window.confirmDeletePayment = function (btn) {
            const form = btn.closest('form');
            if (!form) return;

            const paymentAmount = form.dataset.paymentAmount || '';
            const paymentPeriod = form.dataset.paymentPeriod || '';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar este pago?',
                    html: `<div class="text-left">
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
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                // Fallback to native confirm
                if (confirm('¿Estás seguro de que querés eliminar este pago? Esta acción no se puede deshacer.')) {
                    form.submit();
                }
            }
        };

        // Reusable flash init (auto-dismiss + close)
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

            el.addEventListener('focusin', function () {
                clearTimeout(timer);
            });
        }

        initFlash('flash-success', 'flash-success-close');
        initFlash('flash-error', 'flash-error-close');
    });
    </script>
    @endpush
</x-layouts.app>