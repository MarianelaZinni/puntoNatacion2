<x-layouts.app title="Editar pago">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar Pago</h1>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/30 dark:border-green-800 text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('payments.update', $payment) }}" method="POST" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Alumno -->
                <div>
                    <label for="student_id" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Alumno <span class="text-red-500">*</span>
                    </label>
                    <select id="student_id" name="student_id" required
                            class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                        <option value="">Seleccionar alumno</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id', $payment->student_id) == $student->id ? 'selected' : '' }}>
                                {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Monto -->
                <div>
                    <label for="amount" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Monto (AR$) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required
                           value="{{ old('amount', number_format($payment->amount, 2, '.', '')) }}"
                           class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha de pago -->
                <div>
                    <label for="payment_date" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Fecha de Pago <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="payment_date" name="payment_date" required
                           value="{{ old('payment_date', $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '') }}"
                           class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                    @error('payment_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Período del pago -->
                <div>
                    <label for="payment_period" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Período del Pago <span class="text-red-500">*</span>
                    </label>
                    <input type="month" id="payment_period" name="payment_period" required
                           value="{{ old('payment_period', $paymentPeriodFormatted) }}"
                           class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mes al que corresponde el pago</p>
                    @error('payment_period')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Método de pago -->
                <div>
                    <label for="payment_method_id" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Método de Pago
                    </label>
                    <select id="payment_method_id" name="payment_method_id"
                            class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                        <option value="">— Sin especificar —</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}" {{ old('payment_method_id', $payment->payment_method_id) == $method->id ? 'selected' : '' }}>
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                  <!-- Tipo de pago -->
                <div>
                    <label for="payment_type" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo de Pago <span class="text-red-500">*</span>
                    </label>
                    <select id="payment_type" name="payment_type" required
                            class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                        @foreach(\App\Models\Payment::paymentTypes() as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_type', $payment->payment_type ?? 'normal') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                
                <!-- Notas -->
                <div class="sm:col-span-2">
                    <label for="notes" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Notas
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50"
                              placeholder="Notas adicionales (opcional)">{{ old('notes', $payment->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('payments.history') }}" 
                   class="px-5 py-2 rounded text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 focus:outline-none transition">
                    Cancelar
                </a>
                
                <button type="submit"
                        class="px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                    Actualizar Pago
                </button>
            </div>
        </form>

        <!-- Información adicional sobre el pago -->
        <div class="mt-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Información del Pago</h2>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">ID del Pago:</dt>
                    <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $payment->id }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Creado:</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Última modificación:</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $payment->updated_at ? $payment->updated_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>
                @if($payment->expected_amount)
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Monto esperado (histórico):</dt>
                    <dd class="text-gray-900 dark:text-gray-100 font-medium">${{ number_format($payment->expected_amount, 2, ',', '.') }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</x-layouts.app>