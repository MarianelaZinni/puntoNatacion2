<x-layouts.app title="Crear nuevo profesor">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Nuevo Profesor</h1>
            <a href="{{ route('teachers.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded shadow transition">
                <flux:icon name="arrow-left" class="h-4 w-4" />
                Volver
            </a>
        </div>

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

        <form action="{{ route('teachers.store') }}" method="POST" class="space-y-8 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Nombre --}}
                <div>
                    <label for="name" class="block text-base font-medium text-gray-700 dark:text-gray-300">Nombre <span class="text-red-500">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        value="{{ old('name') }}"
                        placeholder="Ej. Juan"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('name') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                    >
                    @error('name')
                        <p id="name-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Apellido --}}
                <div>
                    <label for="surname" class="block text-base font-medium text-gray-700 dark:text-gray-300">Apellido <span class="text-red-500">*</span></label>
                    <input
                        id="surname"
                        name="surname"
                        type="text"
                        required
                        value="{{ old('surname') }}"
                        placeholder="Ej. Pérez"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('surname') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('surname') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('surname') ? 'surname-error' : '' }}"
                    >
                    @error('surname')
                        <p id="surname-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-base font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="ejemplo@correo.com"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('email') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                    >
                    @error('email')
                        <p id="email-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label for="phone" class="block text-base font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        value="{{ old('phone') }}"
                        placeholder="Ej. +54 11 1234-5678"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('phone') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('phone') ? 'phone-error' : '' }}"
                    >
                    @error('phone')
                        <p id="phone-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha de Nacimiento --}}
                <div>
                    <label for="birth_date" class="block text-base font-medium text-gray-700 dark:text-gray-300">Fecha de Nacimiento</label>
                    <input
                        id="birth_date"
                        name="birth_date"
                        type="date"
                        value="{{ old('birth_date') }}"
                        class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('birth_date') ring-2 ring-red-400 @enderror"
                        aria-invalid="{{ $errors->has('birth_date') ? 'true' : 'false' }}"
                        aria-describedby="{{ $errors->has('birth_date') ? 'birth_date-error' : '' }}"
                    >
                    @error('birth_date')
                        <p id="birth_date-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Dirección --}}
            <div>
                <label for="address" class="block text-base font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    placeholder="Ej. Av. Corrientes 1234, CABA"
                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-[#29b1dc] focus:border-[#29b1dc] text-base leading-relaxed @error('address') ring-2 ring-red-400 @enderror"
                    aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                    aria-describedby="{{ $errors->has('address') ? 'address-error' : '' }}"
                >{{ old('address') }}</textarea>
                @error('address')
                    <p id="address-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('teachers.index') }}"
                   class="px-6 py-2 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 rounded shadow transition">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="px-6 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                    Crear Profesor
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
