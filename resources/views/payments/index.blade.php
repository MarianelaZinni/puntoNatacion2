<x-layouts.app title="Registrar pagos">
    <div class="max-w-4xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Registro de pagos</h1>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-800">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            // Default period value for the month input.
            // Prefer explicit old('payment_period'), otherwise try to derive from old('payment_date'),
            // otherwise default to current month.
            $defaultPeriod = old('payment_period');
            if (!$defaultPeriod) {
                $oldPaymentDate = old('payment_date');
                if ($oldPaymentDate) {
                    try {
                        $defaultPeriod = \Carbon\Carbon::parse($oldPaymentDate)->format('Y-m');
                    } catch (\Throwable $e) {
                        $defaultPeriod = \Carbon\Carbon::now()->format('Y-m');
                    }
                } else {
                    $defaultPeriod = \Carbon\Carbon::now()->format('Y-m');
                }
            }

            // Ensure $selectedStudentId is a simple scalar (avoid Optional objects)
            $selectedStudentId = isset($selectedStudentId) && !is_object($selectedStudentId) ? $selectedStudentId : (old('student_id') ?: null);
        @endphp

        <form action="{{ route('payments.store') }}" method="POST" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Selección de alumno -->
                <div>
                    <label for="student_id" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Alumno
                    </label>
                    <select id="student_id" name="student_id" required
                            class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                        <option value="">Seleccionar alumno</option>
                        @foreach($students as $student)
                            @php
                                $debtRaw = isset($student->debt) ? (float)$student->debt : 0.0;
                                $debtDisplay = number_format($debtRaw, 2, ',', '.');
                                $sel = (string)old('student_id', $selectedStudentId ?? '') === (string)$student->id ? 'selected' : '';
                                $paidThisMonth = !empty($student->paid_this_month) ? '1' : '0';
                            @endphp
                            <option
                                value="{{ $student->id }}"
                                data-debt="{{ number_format($debtRaw, 2, '.', '') }}"
                                data-debt-display="${{ $debtDisplay }}"
                                data-paid-this-month="{{ $paidThisMonth }}"
                                {{ $sel }}
                            >
                                {{ $student->name }} — Adeuda: ${{ $debtDisplay }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        Deuda seleccionada: <span id="selected-debt-display" class="font-medium">—</span>
                        <span id="paid-month-badge" class="ml-2 inline-block text-sm text-green-700 dark:text-green-200 font-medium" style="display:none;">Pagó este mes</span>
                    </div>
                </div>

                <!-- Período del pago (month picker) -->
                <div>
                    <label for="payment_period" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Período (mes/año)
                    </label>
                    <input type="month" id="payment_period" name="payment_period"
                           value="{{ old('payment_period', $defaultPeriod) }}"
                           class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">Seleccioná el mes al que corresponde este pago (YYYY-MM).</p>
                    @error('payment_period')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Monto a pagar -->
                <div>
                    <label for="amount" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Monto a pagar
                    </label>
                    <input type="number" id="amount" name="amount" required min="0" step="0.01"
                           value="{{ old('amount') }}"
                           class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha del pago -->
                <div>
                    <label for="payment_date" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Fecha del pago
                    </label>
                    <input type="date" id="payment_date" name="payment_date"
                           value="{{ old('payment_date', date('Y-m-d')) }}" required
                           class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                    @error('payment_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Método de pago -->
                <div>
                    <label for="payment_method_id" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Método de pago
                    </label>
                    <select id="payment_method_id" name="payment_method_id"
                            class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                        <option value="">Seleccionar método (opcional)</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}" {{ old('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                {{ $pm->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notas (opcional) -->
                <div class="sm:col-span-2">
                    <label for="notes" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Notas (opcional)
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        id="submit-payment-btn"
                        class="px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none">
                    Registrar pago
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        const studentSelect = document.getElementById('student_id');
        const debtDisplay = document.getElementById('selected-debt-display');
        const amountInput = document.getElementById('amount');
        const submitBtn = document.getElementById('submit-payment-btn');
        const paidBadge = document.getElementById('paid-month-badge');

        const formatter = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const hadOldAmount = {!! json_encode(old('amount') ? true : false) !!};

        function parseDebtRaw(opt) {
            if (!opt) return 0;
            const raw = opt.getAttribute('data-debt') || '0';
            const parsed = parseFloat(raw.replace(',', '.')) || 0;
            return parsed;
        }

        function updateSelectedDebt() {
            const opt = studentSelect.options[studentSelect.selectedIndex];
            if (!opt) {
                debtDisplay.textContent = '—';
                paidBadge.style.display = 'none';
                submitBtn.disabled = false;
                return;
            }

            const debtRaw = parseDebtRaw(opt);
            const debtDisplayText = opt.getAttribute('data-debt-display') || (debtRaw > 0 ? ('$' + formatter.format(debtRaw)) : '—');
            const paidThisMonth = opt.getAttribute('data-paid-this-month') === '1';

            if (paidThisMonth) {
                debtDisplay.textContent = '—';
                paidBadge.style.display = 'inline-block';
                if (!hadOldAmount) amountInput.value = '';
                submitBtn.disabled = true;
            } else {
                paidBadge.style.display = 'none';
                debtDisplay.textContent = debtDisplayText;

                if (!hadOldAmount && (!amountInput.value || amountInput.value === '')) {
                    amountInput.value = debtRaw > 0 ? debtRaw.toFixed(2) : '';
                }
                submitBtn.disabled = false;
            }
        }

        if (studentSelect) {
            studentSelect.addEventListener('change', updateSelectedDebt);
            setTimeout(updateSelectedDebt, 20);
        }
    })();
    </script>
    @endpush
</x-layouts.app>