<x-layouts.app title="Pagos por alumno">
   <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Pagos por alumno</h1>

            <div class="flex items-center gap-2">
                @if(!empty($student))
                    <a href="{{ route('reports.payments_by_student.pdf', ['student_id' => $student->id, 'period_from' => $period_from, 'period_to' => $period_to]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]" target="_blank">PDF</a>
                @else
                    <a href="{{ route('reports.payments_by_student.pdf', request()->only(['period_from','period_to'])) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]" target="_blank">PDF (todos)</a>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <form action="{{ route('reports.payments_by_student.view') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="student_id" class="rounded border px-3 py-2">
                    <option value="">Seleccionar alumno (opcional)</option>
                    @foreach(\App\Models\Student::orderBy('name')->get() as $s)
                        <option value="{{ $s->id }}" {{ (!empty($student) && $student->id == $s->id) ? 'selected' : '' }}>
                            {{ $s->name }} @if($s->dni) ({{ $s->dni }}) @endif
                        </option>
                    @endforeach
                </select>

                <label class="text-sm">Desde</label>
                <input type="month" name="period_from" value="{{ $period_from ?? '' }}" class="rounded border px-2 py-1" />

                <label class="text-sm">Hasta</label>
                <input type="month" name="period_to" value="{{ $period_to ?? '' }}" class="rounded border px-2 py-1" />

                <button type="submit" class="px-3 py-2 rounded bg-[#29b1dc] text-white">Actualizar</button>
            </form>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm">
            <div class="mb-3 text-sm text-gray-700 dark:text-gray-200 flex items-center justify-between">
                <div>
                    @if($student)
                        Pagos de: <span class="font-semibold">{{ $student->name }}</span>
                    @else
                        Pagos: <span class="font-semibold">Todos los alumnos</span>
                    @endif
                </div>
                <div class="text-sm text-gray-500">Generado: {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y H:i') }}</div>
            </div>

            @if($payments->isEmpty())
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/40 rounded border border-gray-100 dark:border-zinc-700 text-gray-700 dark:text-gray-200">
                    No se encontraron pagos con los filtros seleccionados.
                </div>
            @else
                <div class="overflow-x-auto">
                    @include('reports.partials._payments_table', ['printMode' => false])
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>