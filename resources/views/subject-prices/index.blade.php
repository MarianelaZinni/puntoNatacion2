<x-layouts.app title="Valores de las clases">
    <div class="max-w-3xl mx-auto py-8 px-4">
        

        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('subject-prices.update') }}" method="POST" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            @csrf

            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                Editá los precios por defecto. Los precios se aplican según la regla del sistema:
                si el alumno tiene al menos una clase con profesor se usará la tarifa "con profesor" sobre el total (tope 5),
                en caso contrario se usará la tarifa "sin profesor".
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-3">Con profesor</h3>
                    <div class="space-y-2">
                        @for($i=1;$i<=5;$i++)
                            <label class="flex items-center justify-between gap-4">
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $i }} vez/semana</span>
                                <input type="number" name="teacher[{{ $i }}]" step="0.01" min="0" value="{{ old('teacher.'.$i, $teacherPrices[$i] ?? null) }}" class="w-40 text-right rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-1">
                            </label>
                        @endfor
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100 mb-3">Sin profesor (pileta libre)</h3>
                    <div class="space-y-2">
                        @for($i=1;$i<=5;$i++)
                            <label class="flex items-center justify-between gap-4">
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $i }} vez/semana</span>
                                <input type="number" name="no_teacher[{{ $i }}]" step="0.01" min="0" value="{{ old('no_teacher.'.$i, $noTeacherPrices[$i] ?? null) }}" class="w-40 text-right rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 px-3 py-1">
                            </label>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="submit" class="px-4 py-2 rounded bg-[#29b1dc] text-white hover:bg-[#24a8cf]">Guardar valores</button>
            </div>
        </form>
    </div>
</x-layouts.app>