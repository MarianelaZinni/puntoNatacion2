<x-layouts.app title="Editar método de pago">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar Método de Pago</h1>
        </div>

        {{-- Mensajes de session (success / error) --}}
        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Lista general de errores --}}
        @if ($errors->any())
            <div class="mb-6 p-4 rounded border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 text-red-800 dark:text-red-200">
                <p class="font-semibold mb-2">Se encontraron los siguientes errores:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

         <form action="{{ route('payment_methods.update', $paymentMethod) }}" method="POST" id="payment-method-form" class="space-y-8 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Nombre --}}
                <div>
                    <label for="name" class="block text-base font-medium text-gray-700 dark:text-gray-300">Descripción <span class="text-red-500">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        value="{{ old('name', $paymentMethod->name) }}"
                        placeholder="Ej. María Pérez"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('name') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                    >
                    @error('name')
                        <p id="name-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                
        {{-- Actions (moved below classes). The Save button submits the payment-method-form via JS. --}}
        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('payment_methods.index') }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                Cancelar
            </a>

            <button
                type="button"
                id="save-btn"
                class="inline-flex items-center px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition"
            >
                <svg id="save-spinner" class="hidden animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Guardar cambios
            </button>
        </div>
    </div>

    @push('scripts')
    <!-- SweetAlert2 (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    (function () {
        const form = document.getElementById('payment-method-form');
        const saveBtn = document.getElementById('save-btn');
        const saveSpinner = document.getElementById('save-spinner');

        // Submit the payment-method form when the external save button is clicked
        saveBtn.addEventListener('click', function (e) {
            // Basic HTML5 validity check before submitting
            if (!form.checkValidity()) {
                // Let browser show validation messages: trigger native reporting
                // by focusing the first invalid element
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.reportValidity();
                    firstInvalid.focus();
                }
                return;
            }
            // Disable button and show spinner immediately to avoid double submissions
            saveBtn.disabled = true;
            saveSpinner.classList.remove('hidden');
            form.submit();
        });

        // If there are server side errors, highlight the first invalid field and scroll to it
        @if ($errors->any())
            (function () {
                const firstErrorEl = document.querySelector('.ring-2.ring-red-400, [aria-invalid="true"]');
                if (firstErrorEl) {
                    firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorEl.focus();
                }
            })();
        @endif

        // Flash close handlers
        document.getElementById('flash-success-close')?.addEventListener('click', function(){ this.closest('[id^=flash-]')?.remove(); });
        document.getElementById('flash-error-close')?.addEventListener('click', function(){ this.closest('[id^=flash-]')?.remove(); });
    })();
    </script>
    @endpush
</x-layouts.app>