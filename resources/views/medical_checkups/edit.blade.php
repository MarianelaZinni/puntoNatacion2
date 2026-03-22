<x-layouts.app title="Editar revisión médica">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar Revisión Médica</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Alumno: <span class="font-semibold">{{ $medicalCheckup->student->name }}</span>
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

        <form action="{{ route('medical_checkups.update', $medicalCheckup) }}" method="POST" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Fecha de Revisión -->
                <div>
                    <label for="checkup_date" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Fecha de Revisión <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="checkup_date" name="checkup_date" required
                           value="{{ old('checkup_date', $medicalCheckup->checkup_date ? $medicalCheckup->checkup_date->format('Y-m-d') : '') }}"
                           class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                    @error('checkup_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Período (mes/año al que corresponde) -->
                <div>
                    <label for="period" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Período (Mes/Año) <span class="text-red-500">*</span>
                    </label>
                    <input type="month" id="period" name="period" required
                           value="{{ old('period', $periodFormatted) }}"
                           class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mes/año al que corresponde la revisión</p>
                    @error('period')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado (Aprobada/No aprobada) -->
                <div class="sm:col-span-2">
                    <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Estado de la Revisión <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center">
                            <input type="radio" name="approved" value="1" required
                                   {{ old('approved', $medicalCheckup->approved ? '1' : '0') === '1' ? 'checked' : '' }}
                                   class="h-4 w-4 text-[#29b1dc] border-gray-300 dark:border-zinc-700 focus:ring-[#29b1dc]">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Aprobada</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="approved" value="0" required
                                   {{ old('approved', $medicalCheckup->approved ? '1' : '0') === '0' ? 'checked' : '' }}
                                   class="h-4 w-4 text-[#29b1dc] border-gray-300 dark:border-zinc-700 focus:ring-[#29b1dc]">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">No Aprobada</span>
                        </label>
                    </div>
                    @error('approved')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones -->
                <div class="sm:col-span-2">
                    <label for="observations" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Observaciones
                    </label>
                    <textarea id="observations" name="observations" rows="4"
                              class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50"
                              placeholder="Observaciones adicionales (opcional)">{{ old('observations', $medicalCheckup->observations) }}</textarea>
                    @error('observations')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('medical_checkups.index', ['student_id' => $medicalCheckup->student_id]) }}" 
                   class="px-5 py-2 rounded text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 focus:outline-none transition">
                    Cancelar
                </a>
                
                <button type="submit"
                        class="px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                    Actualizar Revisión
                </button>
            </div>
        </form>

        <!-- Información adicional -->
        <div class="mt-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Información del Registro</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">ID:</dt>
                    <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $medicalCheckup->id }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Creado:</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $medicalCheckup->created_at ? $medicalCheckup->created_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Última modificación:</dt>
                    <dd class="text-gray-900 dark:text-gray-100">{{ $medicalCheckup->updated_at ? $medicalCheckup->updated_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-layouts.app>