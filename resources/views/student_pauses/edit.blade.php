<x-layouts.app title="Editar Período de Pausa">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar Período de Pausa</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Alumno: <span class="font-semibold">{{ $studentPause->student->name }}</span>
                </p>
            </div>
        </div>

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

        <form action="{{ route('student_pauses.update', $studentPause) }}" method="POST"
              class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="pause_period" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Período (Mes/Año) <span class="text-red-500">*</span>
                </label>
                <input type="month" id="pause_period" name="pause_period" required
                       value="{{ old('pause_period', $periodFormatted) }}"
                       class="block w-full max-w-xs rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Durante este mes, el alumno no generará deuda.
                </p>
                @error('pause_period')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('student_pauses.index', ['student_id' => $studentPause->student_id]) }}"
                   class="px-5 py-2 rounded text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 focus:outline-none transition">
                    Cancelar
                </a>

                <button type="submit"
                        class="px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
