<x-layouts.app title="Todos los alumnos">
     <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Todos los alumnos</h1>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.all_students.pdf') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">PDF</a>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <div class="mb-3 text-sm text-gray-700 dark:text-gray-200 flex items-center justify-between">
                <div>
                    Listado completo de alumnos
                </div>
                <div class="text-sm text-gray-500">Generado: {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y H:i') }}</div>
            </div>

            @if($students->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                    No hay alumnos registrados.
                </div>
            @else
                <div class="overflow-x-auto">
                    @include('reports.partials._students_table', ['printMode' => false])
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>