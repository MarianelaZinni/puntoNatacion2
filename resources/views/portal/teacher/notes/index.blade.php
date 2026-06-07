<x-layouts.app title="Notas de alumnos">
    <div class="mx-auto max-w-5xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Notas de alumnos</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $subject->subjectType?->description ?? 'Clase' }} · {{ $subject->day }} {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
                </p>
            </div>
            <a href="{{ route('portal.teacher') }}" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Volver</a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Nueva nota</h2>
            <form action="{{ route('portal.teacher.notes.store', $subject) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="student_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Alumno</label>
                    <select id="student_id" name="student_id" required class="w-full rounded border-gray-300 bg-white text-gray-900 focus:ring-[#29b1dc] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100">
                        <option value="">Seleccionar alumno...</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                {{ $student->name }} @if($student->dni) (DNI {{ $student->dni }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="title" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Título (opcional)</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:ring-[#29b1dc] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100">
                </div>
                <div>
                    <label for="body" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nota</label>
                    <textarea id="body" name="body" rows="5" required class="w-full rounded border-gray-300 bg-white text-gray-900 focus:ring-[#29b1dc] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100">{{ old('body') }}</textarea>
                </div>
                <button type="submit" class="rounded bg-[#29b1dc] px-3 py-2 text-sm text-white hover:bg-[#24a8cf]">Guardar nota</button>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Historial de notas</h2>
            @forelse ($notes as $note)
                <article class="mb-4 rounded border border-gray-100 p-4 last:mb-0 dark:border-zinc-800">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $note->student?->name ?? 'Alumno eliminado' }}
                                @if($note->title) — {{ $note->title }} @endif
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $note->created_at->format('d/m/Y H:i') }} · {{ $note->author?->name ?? 'Profesor' }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('portal.teacher.notes.edit', [$subject, $note]) }}" class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Editar</a>
                            <form action="{{ route('portal.teacher.notes.destroy', [$subject, $note]) }}" method="POST" onsubmit="return confirm('¿Eliminar esta nota?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded bg-red-500 px-2 py-1 text-xs text-white hover:bg-red-600">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $note->body }}</p>
                </article>
            @empty
                <p class="text-sm text-gray-400">Todavía no hay notas para esta clase.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
