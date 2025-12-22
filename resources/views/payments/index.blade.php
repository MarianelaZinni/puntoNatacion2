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

                <!-- Período del pago (select con sólo periodos adeudados + periodo actual si NO está pagado) -->
                <div>
                    <label for="payment_period" class="block text-base font-medium text-gray-700 dark:text-gray-300">
                        Período (mes/año)
                    </label>

                    <select id="payment_period" name="payment_period"
                            class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm">
                        {{-- será poblado por JS --}}
                        <option value="{{ $defaultPeriod }}">{{ $defaultPeriod }}</option>
                    </select>

                    <p class="text-xs text-gray-500 mt-1">Seleccioná el mes adeudado a pagar. Incluimos el período actual solo si no está totalmente pagado.</p>
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
        const periodSelect = document.getElementById('payment_period');

        const formatter = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const hadOldAmount = {!! json_encode(old('amount') ? true : false) !!};
        const hadOldPeriod = {!! json_encode(old('payment_period') ? true : false) !!};
        const todayYm = new Date().toISOString().slice(0,7); // YYYY-MM

        function parseDebtRaw(opt) {
            if (!opt) return 0;
            const raw = opt.getAttribute('data-debt') || '0';
            const parsed = parseFloat(raw.replace(',', '.')) || 0;
            return parsed;
        }

        function buildOption(p) {
            const opt = document.createElement('option');
            opt.value = p.period;
            opt.textContent = `${p.period} — Adeuda: $${formatter.format(p.deficit)}`;
            opt.setAttribute('data-deficit', (p.deficit || 0));
            return opt;
        }

        function populatePeriodSelectFromSelectable(selectableArray, paidThisMonthFlag) {
            // selectableArray: array of {period, paid, deficit}
            periodSelect.innerHTML = '';

            const currentYm = todayYm;
            let list = Array.isArray(selectableArray) ? selectableArray.slice() : [];

            // If the current period is fully paid for this student (paidThisMonthFlag === true),
            // REMOVE the current period from the selectable list.
            if (paidThisMonthFlag) {
                list = list.filter(x => x.period !== currentYm);
            } else {
                // ensure current is first (if present) otherwise we will later insert default
                if (!list.find(x => x.period === currentYm)) {
                    // Insert current with deficit 0 (will be overwritten if real deficit known)
                    list.unshift({ period: currentYm, paid: 0, deficit: 0 });
                } else {
                    // move current to the front
                    list = list.filter(x => x.period !== currentYm);
                    list.unshift(selectableArray.find(x => x.period === currentYm));
                }
            }

            // After filtering, if no selectable periods remain, show a disabled option.
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

            // populate the options
            list.forEach((p, idx) => {
                const opt = buildOption(p);
                if (idx === 0) opt.selected = true;
                periodSelect.appendChild(opt);
            });

            periodSelect.disabled = false;
        }

        function updateSelectedDebt() {
            const opt = studentSelect.options[studentSelect.selectedIndex];
            if (!opt) {
                debtDisplay.textContent = '—';
                paidBadge.style.display = 'none';
                periodSelect.innerHTML = `<option value="">Seleccionar alumno primero</option>`;
                periodSelect.disabled = true;
                submitBtn.disabled = false;
                return;
            }

            const debtRaw = parseDebtRaw(opt);
            const debtDisplayText = opt.getAttribute('data-debt-display') || (debtRaw > 0 ? ('$' + formatter.format(debtRaw)) : '—');
            const paidThisMonth = opt.getAttribute('data-paid-this-month') === '1';
            const monthlyAmount = parseFloat(opt.getAttribute('data-monthly-amount') || 0) || 0;
            const selectableJson = opt.getAttribute('data-selectable') || '[]';
            let selectable;
            try { selectable = JSON.parse(selectableJson || '[]'); } catch (e) { selectable = []; }

            // Mostrar deuda total
            debtDisplay.textContent = debtDisplayText;

            // Mostrar badge si pagó mes actual
            if (paidThisMonth) {
                paidBadge.style.display = 'inline-block';
            } else {
                paidBadge.style.display = 'none';
            }

            // Poblar select con periodos seleccionables; si pagó este mes se excluye el mes actual
            populatePeriodSelectFromSelectable(selectable, paidThisMonth);

            // Precargar amount si no hay old value: usar deficit del periodo seleccionado
            if (!hadOldPeriod) {
                const selOpt = periodSelect.options[periodSelect.selectedIndex];
                if (selOpt && !periodSelect.disabled) {
                    const deficit = parseFloat(selOpt.getAttribute('data-deficit') || 0) || 0;
                    if (!hadOldAmount && (!amountInput.value || amountInput.value === '')) {
                        if (deficit > 0) {
                            amountInput.value = deficit.toFixed(2);
                        } else if (selOpt.value === todayYm && monthlyAmount > 0) {
                            // allow paying current month even if deficit 0
                            amountInput.value = monthlyAmount.toFixed(2);
                        } else {
                            amountInput.value = debtRaw > 0 ? debtRaw.toFixed(2) : '';
                        }
                    }
                }
            }

            // Habilitar / deshabilitar submit según deuda total o posibilidad de pago del periodo actual.
            // Permitimos registrar pago si existe deuda total o si monthlyAmount>0 (para permitir pago del mes actual)
            submitBtn.disabled = (debtRaw <= 0 && monthlyAmount <= 0);
        }

        // Cuando cambie el periodo seleccionado actualizamos monto si el usuario no puso nada
        periodSelect.addEventListener('change', function () {
            const opt = periodSelect.options[periodSelect.selectedIndex];
            if (!opt) return;
            const deficit = parseFloat(opt.getAttribute('data-deficit') || 0) || 0;
            if (!hadOldAmount && (!amountInput.value || amountInput.value === '')) {
                if (deficit > 0) amountInput.value = deficit.toFixed(2);
                else {
                    const studentOpt = studentSelect.options[studentSelect.selectedIndex];
                    const monthlyAmount = parseFloat(studentOpt.getAttribute('data-monthly-amount') || 0) || 0;
                    if (opt.value === todayYm && monthlyAmount > 0) {
                        amountInput.value = monthlyAmount.toFixed(2);
                    }
                }
            }
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