<x-layouts.app title="Editar Profesor">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar Profesor</h1>
        </div>

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

        <form action="{{ route('teachers.update', $teacher) }}" method="POST" id="teacher-form" class="space-y-8 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Nombre --}}
                <div>
                    <label for="name" class="block text-base font-medium text-gray-700 dark:text-gray-300">Nombre completo <span class="text-red-500">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        value="{{ old('name', $teacher->name) }}"
                        placeholder="Ej. Juan García"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('name') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DNI --}}
                <div>
                    <label for="dni" class="block text-base font-medium text-gray-700 dark:text-gray-300">DNI / Documento</label>
                    <input
                        id="dni"
                        name="dni"
                        type="text"
                        value="{{ old('dni', $teacher->dni) }}"
                        placeholder="Ej. 12345678"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('dni') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('dni') ? 'true' : 'false' }}"
                    >
                    @error('dni')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-base font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $teacher->email) }}"
                        placeholder="ejemplo@dominio.com"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('email') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label for="phone" class="block text-base font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone', $teacher->phone) }}"
                        placeholder="+54 9 11 1234 5678"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('phone') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div class="sm:col-span-2">
                    <label for="address" class="block text-base font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                    <input
                        id="address"
                        name="address"
                        type="text"
                        value="{{ old('address', $teacher->address) }}"
                        placeholder="Calle, número, ciudad"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('address') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                    >
                    @error('address')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Observaciones --}}
                <div class="sm:col-span-2">
                    <label for="observations" class="block text-base font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                    <textarea
                        id="observations"
                        name="observations"
                        rows="3"
                        placeholder="Notas adicionales sobre el profesor..."
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('observations') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('observations') ? 'true' : 'false' }}"
                    >{{ old('observations', $teacher->observations) }}</textarea>
                    @error('observations')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="sm:col-span-2 flex items-center justify-end gap-3">
                    <a href="{{ route('teachers.index') }}" class="inline-flex items-center px-5 py-2 rounded text-white bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500 transition">
                        Cancelar
                    </a>

                    <button
                        type="submit"
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
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        const form = document.getElementById('teacher-form');
        const saveBtn = document.getElementById('save-btn');
        const saveSpinner = document.getElementById('save-spinner');

        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;
            saveBtn.disabled = true;
            saveSpinner.classList.remove('hidden');
        });

        @if ($errors->any())
            (function () {
                const firstErrorEl = document.querySelector('.ring-2.ring-red-400, [aria-invalid="true"]');
                if (firstErrorEl) {
                    firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorEl.focus();
                }
            })();
        @endif
    })();
    </script>
    @endpush
</x-layouts.app>
