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

                <!-- Período del pago (sólo periodos sin ningún pago registrado) -->
                <div>
                    <label for="payment_period" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Período (mes/año)
                    </label>

                    <select id="payment_period" name="payment_period"
                            class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                        {{-- será poblado por JS --}}
                        <option value="{{ $defaultPeriod }}">{{ $defaultPeriod }}</option>
                    </select>

                    <p class="text-xs text-gray-500 mt-1">Se muestran solo los períodos sin pago registrado.</p>
                    @error('payment_period')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de pago -->
                <div class="sm:col-span-2">
                    <span class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo de pago
                    </span>
                    <div id="payment-type-group" class="flex flex-wrap gap-3">
                        <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-md border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 hover:border-[#29b1dc] has-[:checked]:border-[#29b1dc] has-[:checked]:bg-[#29b1dc]/10 transition">
                            <input type="radio" name="payment_type" value="normal" class="accent-[#29b1dc]" checked>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Pago normal</span>
                            <span id="label-normal" class="ml-1 text-sm text-gray-500 dark:text-gray-400"></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-md border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 hover:border-[#29b1dc] has-[:checked]:border-[#29b1dc] has-[:checked]:bg-[#29b1dc]/10 transition">
                            <input type="radio" name="payment_type" value="medio_mes" class="accent-[#29b1dc]">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Pago medio mes</span>
                            <span id="label-medio-mes" class="ml-1 text-sm text-gray-500 dark:text-gray-400"></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-md border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 hover:border-[#29b1dc] has-[:checked]:border-[#29b1dc] has-[:checked]:bg-[#29b1dc]/10 transition">
                            <input type="radio" name="payment_type" value="con_recargo" class="accent-[#29b1dc]">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Pago con recargo</span>
                            <span id="label-con-recargo" class="ml-1 text-sm text-gray-500 dark:text-gray-400"></span>
                        </label>
                    </div>
                </div>

                <!-- Monto a pagar -->
                <div>
                    <label for="amount" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Monto a pagar
                    </label>
                    <input type="number" id="amount" name="amount" required min="0" step="0.01"
                           value="{{ old('amount') }}"
                           class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">Se completa automáticamente según el tipo de pago seleccionado. Podés modificarlo.</p>
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
        const periodSelect = document.getElementById('payment_period');
        const paymentTypeRadios = document.querySelectorAll('input[name="payment_type"]');

        // Porcentaje de recargo configurado en el servidor
        const surchargePercentage = {!! json_encode((float) $surchargePercentage) !!};

        const formatter = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const hadOldAmount = {!! json_encode(old('amount') ? true : false) !!};
        const todayYm = new Date().toISOString().slice(0,7); // YYYY-MM

        // Calcula los montos de los 3 tipos de pago a partir del monto mensual
        function getPaymentAmounts(monthlyAmount) {
            const normal = monthlyAmount;
            const medioMes = monthlyAmount / 2;
            const conRecargo = monthlyAmount * (1 + surchargePercentage / 100);
            return { normal, medioMes, conRecargo };
        }

        // Actualiza las etiquetas de los tipos de pago con los montos calculados
        function updatePaymentTypeLabels(monthlyAmount) {
            const amounts = getPaymentAmounts(monthlyAmount);
            const labelNormal = document.getElementById('label-normal');
            const labelMedioMes = document.getElementById('label-medio-mes');
            const labelConRecargo = document.getElementById('label-con-recargo');

            if (monthlyAmount > 0) {
                labelNormal.textContent = `($${formatter.format(amounts.normal)})`;
                labelMedioMes.textContent = `($${formatter.format(amounts.medioMes)})`;
                labelConRecargo.textContent = `($${formatter.format(amounts.conRecargo)} — ${surchargePercentage}% recargo)`;
            } else {
                labelNormal.textContent = '';
                labelMedioMes.textContent = '';
                labelConRecargo.textContent = '';
            }
        }

        // Devuelve el monto correspondiente al tipo de pago seleccionado
        function getSelectedPaymentAmount(monthlyAmount) {
            const checked = document.querySelector('input[name="payment_type"]:checked');
            const type = checked ? checked.value : 'normal';
            const amounts = getPaymentAmounts(monthlyAmount);
            if (type === 'medio_mes') return amounts.medioMes;
            if (type === 'con_recargo') return amounts.conRecargo;
            return amounts.normal;
        }

        function buildOption(p) {
            const opt = document.createElement('option');
            opt.value = p.period;
            // Mostrar solo el periodo (sin monto de déficit, según nuevo criterio)
            opt.textContent = p.period;
            return opt;
        }

        function populatePeriodSelect(selectableArray, paidThisMonthFlag) {
            periodSelect.innerHTML = '';
            let list = Array.isArray(selectableArray) ? selectableArray.slice() : [];

            // Si el alumno ya pagó el mes actual, excluirlo de la lista
            if (paidThisMonthFlag) {
                list = list.filter(x => x.period !== todayYm);
            }

            if (!list.length) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No hay periodos impagos para este alumno';
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

        function getCurrentMonthlyAmount() {
            const opt = studentSelect.options[studentSelect.selectedIndex];
            if (!opt) return 0;
            return parseFloat(opt.getAttribute('data-monthly-amount') || 0) || 0;
        }

        function updateAmountFromPaymentType() {
            const monthlyAmount = getCurrentMonthlyAmount();
            if (monthlyAmount > 0) {
                amountInput.value = getSelectedPaymentAmount(monthlyAmount).toFixed(2);
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
                updatePaymentTypeLabels(0);
                return;
            }

            const debtRaw = parseFloat(opt.getAttribute('data-debt') || '0') || 0;
            const debtDisplayText = opt.getAttribute('data-debt-display') || (debtRaw > 0 ? ('$' + formatter.format(debtRaw)) : '—');
            const paidThisMonth = opt.getAttribute('data-paid-this-month') === '1';
            const monthlyAmount = parseFloat(opt.getAttribute('data-monthly-amount') || 0) || 0;
            const selectableJson = opt.getAttribute('data-selectable') || '[]';
            let selectable;
            try { selectable = JSON.parse(selectableJson || '[]'); } catch (e) { selectable = []; }

            // Mostrar deuda total
            debtDisplay.textContent = debtDisplayText;

            // Mostrar badge si pagó mes actual
            paidBadge.style.display = paidThisMonth ? 'inline-block' : 'none';

            // Poblar select con periodos sin ningún pago
            populatePeriodSelect(selectable, paidThisMonth);

            // Actualizar etiquetas de tipos de pago con montos calculados
            updatePaymentTypeLabels(monthlyAmount);

            // Precargar monto según tipo de pago seleccionado (si no hay old value)
            if (!hadOldAmount) {
                if (monthlyAmount > 0) {
                    amountInput.value = getSelectedPaymentAmount(monthlyAmount).toFixed(2);
                } else {
                    amountInput.value = '';
                }
            }

            submitBtn.disabled = (periodSelect.disabled && debtRaw <= 0);
        }

        // Al cambiar tipo de pago, actualizar monto automáticamente
        paymentTypeRadios.forEach(radio => {
            radio.addEventListener('change', updateAmountFromPaymentType);
        });

        if (studentSelect) {
            studentSelect.addEventListener('change', updateSelectedDebt);
            // initialize on load if a student is preselected
            setTimeout(updateSelectedDebt, 20);
        }
    })();
    </script>
    @endpush
</x-layouts.app>