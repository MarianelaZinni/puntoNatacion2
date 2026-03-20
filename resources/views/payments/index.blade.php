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
            // Default selected period fallback (string 'YYYY-MM')
            $defaultPeriod = old('payment_period') ?: \Carbon\Carbon::now()->format('Y-m');
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
                                $selectable = $student->selectable_periods ?? [];
                            @endphp
                            <option
                                value="{{ $student->id }}"
                                data-debt="{{ number_format($debtRaw, 2, '.', '') }}"
                                data-debt-display="${{ $debtDisplay }}"
                                data-paid-this-month="{{ $paidThisMonth }}"
                                data-monthly-amount="{{ number_format($student->monthly_amount ?? 0, 2, '.', '') }}"
                                data-selectable='@json($selectable)'
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
                        Deuda total: <span id="selected-debt-display" class="font-medium">—</span>
                        <span id="paid-month-badge" class="ml-2 inline-block text-sm text-green-700 dark:text-green-200 font-medium" style="display:none;">Pagó este mes</span>
                    </div>
                </div>

                <!-- Período del pago (periodos sin ningún pago registrado) -->
                <div>
                    <label for="payment_period" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Período (mes/año)
                    </label>

                    <select id="payment_period" name="payment_period"
                            class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                        {{-- será poblado por JS --}}
                        <option value="{{ $defaultPeriod }}">{{ $defaultPeriod }}</option>
                    </select>

                    <p class="text-xs text-gray-500 mt-1">Solo se muestran los períodos sin ningún pago registrado.</p>
                    @error('payment_period')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de pago -->
                <div>
                    <label for="payment_type" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Tipo de pago
                    </label>
                    <select id="payment_type" name="payment_type" required
                            class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                        <option value="normal"      {{ old('payment_type', 'normal') === 'normal'      ? 'selected' : '' }}>Pago normal</option>
                        <option value="medio_mes"   {{ old('payment_type') === 'medio_mes'   ? 'selected' : '' }}>Pago medio mes</option>
                        <option value="con_recargo" {{ old('payment_type') === 'con_recargo' ? 'selected' : '' }}>Pago con recargo ({{ number_format(config('business.surcharge_rate', 0.10) * 100, 0) }}%)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">El monto se calcula automáticamente según el tipo elegido.</p>
                    @error('payment_type')
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
        const studentSelect   = document.getElementById('student_id');
        const debtDisplay     = document.getElementById('selected-debt-display');
        const amountInput     = document.getElementById('amount');
        const submitBtn       = document.getElementById('submit-payment-btn');
        const paidBadge       = document.getElementById('paid-month-badge');
        const periodSelect    = document.getElementById('payment_period');
        const paymentTypeSelect = document.getElementById('payment_type');

        const formatter     = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const hadOldAmount  = {!! json_encode(old('amount') ? true : false) !!};
        const hadOldPeriod  = {!! json_encode(old('payment_period') ? true : false) !!};
        const todayYm       = new Date().toISOString().slice(0,7); // YYYY-MM
        const surchargeRate = {!! json_encode((float) config('business.surcharge_rate', 0.10)) !!};

        /**
         * Calcula el monto según el tipo de pago y la cuota mensual.
         */
        function calculateAmountForType(monthlyAmount, paymentType) {
            if (!monthlyAmount || monthlyAmount <= 0) return 0;
            switch (paymentType) {
                case 'medio_mes':   return monthlyAmount / 2;
                case 'con_recargo': return monthlyAmount * (1 + surchargeRate);
                default:            return monthlyAmount; // normal
            }
        }

        function parseDebtRaw(opt) {
            if (!opt) return 0;
            const raw = opt.getAttribute('data-debt') || '0';
            return parseFloat(raw.replace(',', '.')) || 0;
        }

        function buildOption(p) {
            const opt = document.createElement('option');
            opt.value = p.period;
            const displayAmount = p.monthly_amount || p.deficit || 0;
            opt.textContent = `${p.period} — Cuota: $${formatter.format(displayAmount)}`;
            opt.setAttribute('data-deficit',        (p.deficit        || p.monthly_amount || 0));
            opt.setAttribute('data-monthly-amount', (p.monthly_amount || p.deficit        || 0));
            return opt;
        }

        function populatePeriodSelectFromSelectable(selectableArray, paidThisMonthFlag) {
            periodSelect.innerHTML = '';

            let list = Array.isArray(selectableArray) ? selectableArray.slice() : [];

            // Si el alumno ya pagó el mes actual, excluirlo de la lista
            if (paidThisMonthFlag) {
                list = list.filter(x => x.period !== todayYm);
            } else {
                // Asegurar que el mes actual esté primero si no está ya en la lista
                const currentEntry = list.find(x => x.period === todayYm);
                if (!currentEntry) {
                    // No lo agregamos si paidThisMonthFlag es false y no está: no hace falta
                    // El modelo ya lo incluye en selectable_periods cuando no tiene pago
                } else {
                    // Mover el mes actual al frente
                    list = [currentEntry, ...list.filter(x => x.period !== todayYm)];
                }
            }

            if (!list.length) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No hay periodos disponibles para este alumno';
                opt.disabled = true;
                opt.selected = true;
                periodSelect.appendChild(opt);
                periodSelect.disabled = true;
                return;
            }

            list.forEach((p, idx) => {
                const opt = buildOption(p);
                if (idx === 0) opt.selected = true;
                periodSelect.appendChild(opt);
            });

            periodSelect.disabled = false;
        }

        /**
         * Actualiza el campo monto según el período y tipo de pago seleccionados.
         */
        function refreshAmount() {
            if (hadOldAmount) return; // respetar valor anterior si venimos de un error de validación

            const studentOpt    = studentSelect.options[studentSelect.selectedIndex];
            const periodOpt     = periodSelect.options[periodSelect.selectedIndex];
            const paymentType   = paymentTypeSelect ? paymentTypeSelect.value : 'normal';

            if (!studentOpt || !studentOpt.value || !periodOpt || !periodOpt.value) return;

            // Preferir monthly_amount del periodo; caer en monthly_amount del alumno
            const periodMonthly = parseFloat(periodOpt.getAttribute('data-monthly-amount') || 0) || 0;
            const studentMonthly = parseFloat(studentOpt.getAttribute('data-monthly-amount') || 0) || 0;
            const monthlyAmount = periodMonthly || studentMonthly;

            const calculatedAmount = calculateAmountForType(monthlyAmount, paymentType);
            if (calculatedAmount > 0) {
                amountInput.value = calculatedAmount.toFixed(2);
            }
        }

        function updateSelectedDebt() {
            const opt = studentSelect.options[studentSelect.selectedIndex];
            if (!opt || !opt.value) {
                debtDisplay.textContent = '—';
                paidBadge.style.display = 'none';
                periodSelect.innerHTML = `<option value="">Seleccionar alumno primero</option>`;
                periodSelect.disabled = true;
                submitBtn.disabled = false;
                return;
            }

            const debtRaw         = parseDebtRaw(opt);
            const debtDisplayText = opt.getAttribute('data-debt-display') || (debtRaw > 0 ? ('$' + formatter.format(debtRaw)) : '—');
            const paidThisMonth   = opt.getAttribute('data-paid-this-month') === '1';
            const monthlyAmount   = parseFloat(opt.getAttribute('data-monthly-amount') || 0) || 0;
            const selectableJson  = opt.getAttribute('data-selectable') || '[]';
            let selectable;
            try { selectable = JSON.parse(selectableJson || '[]'); } catch (e) { selectable = []; }

            debtDisplay.textContent = debtDisplayText;

            if (paidThisMonth) {
                paidBadge.style.display = 'inline-block';
            } else {
                paidBadge.style.display = 'none';
            }

            populatePeriodSelectFromSelectable(selectable, paidThisMonth);

            if (!hadOldPeriod) {
                refreshAmount();
            }

            submitBtn.disabled = (debtRaw <= 0 && monthlyAmount <= 0);
        }

        // Recalcular monto cuando cambia el período o el tipo de pago
        periodSelect.addEventListener('change', function () {
            if (!hadOldAmount) refreshAmount();
        });

        if (paymentTypeSelect) {
            paymentTypeSelect.addEventListener('change', function () {
                refreshAmount();
            });
        }

        if (studentSelect) {
            studentSelect.addEventListener('change', updateSelectedDebt);
            setTimeout(updateSelectedDebt, 20);
        }
    })();
    </script>
    @endpush
</x-layouts.app>