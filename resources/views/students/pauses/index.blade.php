<x-layouts.app title="Períodos de pausa — {{ $student->name }}">
    <div class="max-w-3xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Períodos de pausa</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Alumno: <span class="font-medium">{{ $student->name }}</span>
                    @if($student->dni) &mdash; DNI {{ $student->dni }}@endif
                </p>
            </div>
            <a href="{{ route('students.index') }}"
               class="inline-flex items-center px-4 py-2 rounded text-sm text-white bg-gray-500 hover:bg-gray-600 transition">
                ← Volver
            </a>
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

        {{-- Create form --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Agregar nuevo período de pausa</h2>

            <form action="{{ route('students.pauses.store', $student) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha inicio <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="start_date" id="start_date"
                               value="{{ old('start_date') }}"
                               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                               required>
                        @error('start_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha fin <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="end_date" id="end_date"
                               value="{{ old('end_date') }}"
                               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]"
                               required>
                        @error('end_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Motivo (opcional)
                        </label>
                        <input type="text" name="reason" id="reason"
                               value="{{ old('reason') }}"
                               placeholder="Ej: Viaje, lesión, etc."
                               class="w-full px-3 py-2 rounded border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#29b1dc]">
                        @error('reason')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                        Guardar pausa
                    </button>
                </div>
            </form>
        </div>

        {{-- List of pauses --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-3 bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                    {{ $pauses->count() }} período(s) registrado(s)
                </span>
            </div>

            @if($pauses->isEmpty())
                <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    No hay períodos de pausa registrados para este alumno.
                </div>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($pauses as $pause)
                    @php
                        $editable = $pause->isEditable();
                        $active   = $pause->isActive();
                    @endphp
                    <li class="flex items-center justify-between px-6 py-4">
                        <div class="flex items-center gap-4">
                            {{-- Status indicator --}}
                            @if($active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    Activa
                                </span>
                            @elseif($pause->end_date->lt(\Carbon\Carbon::today()))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 dark:bg-zinc-700 dark:text-zinc-400">
                                    Finalizada
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    Futura
                                </span>
                            @endif

                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $pause->start_date->format('d/m/Y') }}
                                    &ndash;
                                    {{ $pause->end_date->format('d/m/Y') }}
                                </p>
                                @if($pause->reason)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $pause->reason }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($editable)
                                <a href="{{ route('students.pauses.edit', [$student, $pause]) }}"
                                   class="inline-flex items-center justify-center h-8 w-8 rounded-full text-yellow-600 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition"
                                   title="Editar">
                                    <flux:icon name="pencil-square" class="h-4 w-4" />
                                </a>

                                <form action="{{ route('students.pauses.destroy', [$student, $pause]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="confirmDeletePause(this)"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-full text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition"
                                            title="Eliminar">
                                        <flux:icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500 italic">No editable</span>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    function confirmDeletePause(btn) {
        Swal.fire({
            title: '¿Eliminar pausa?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    }
    </script>
    @endpush
</x-layouts.app>
