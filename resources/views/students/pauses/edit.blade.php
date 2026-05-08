<x-layouts.app title="Editar pausa — {{ $student->name }}">
    <div class="max-w-xl mx-auto py-8 px-4">

        {{-- Header --}}
         <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar período de pausa</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Alumno: <span class="font-medium">{{ $student->name }}</span>
                </p>
            </div>
            <a href="{{ route('students.pauses.index', $student) }}"
               class="inline-flex items-center px-4 py-2 rounded text-sm text-white bg-gray-500 hover:bg-gray-600 transition">
                ← Volver
            </a>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6">
            <form action="{{ route('students.pauses.update', [$student, $pause]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Mes de pausa <span class="text-red-500">*</span>
                        </label>
                        <input type="month" name="start_date" id="start_date"
                               value="{{ old('start_date', $pause->start_date->format('Y-m')) }}"
                               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                               required>
                        @error('start_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Motivo (opcional)
                        </label>
                        <input type="text" name="reason" id="reason"
                               value="{{ old('reason', $pause->reason) }}"
                               placeholder="Ej: Viaje, lesión, etc."
                               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]">
                        @error('reason')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('students.pauses.index', $student) }}"
                       class="px-4 py-2 rounded text-sm text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-zinc-700 hover:bg-gray-200 dark:hover:bg-zinc-600 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                        Actualizar pausa
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>