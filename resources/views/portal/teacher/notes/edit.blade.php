<x-layouts.app title="Editar nota">
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar nota</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $subject->subjectType?->description ?? 'Clase' }} · {{ $subject->day }} {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
            </p>
        </div>

        <form action="{{ route('portal.teacher.notes.update', [$subject, $note]) }}" method="POST" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            @csrf
            @method('PUT')

            <div>
                <label for="student_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Destinatario</label>
                <select id="student_id" name="student_id" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:ring-[#29b1dc] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100">
                    <option value="" @selected(old('student_id', $note->student_id) === null)>📢 Toda la clase</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected(old('student_id', $note->student_id) == $student->id)>
                            {{ $student->name }} @if($student->dni) (DNI {{ $student->dni }}) @endif
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Dejá "Toda la clase" para que la nota sea visible para todos los alumnos de esta clase.</p>
            </div>

            <div>
                <label for="title" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Título (opcional)</label>
                <input type="text" id="title" name="title" value="{{ old('title', $note->title) }}" class="w-full rounded border-gray-300 bg-white text-gray-900 focus:ring-[#29b1dc] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100">
            </div>

            <div>
                <label for="body" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nota</label>
                <textarea id="body" name="body" rows="6" required class="w-full rounded border-gray-300 bg-white text-gray-900 focus:ring-[#29b1dc] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100">{{ old('body', $note->body) }}</textarea>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('portal.teacher.notes.index', $subject) }}" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Cancelar</a>
                <button type="submit" class="rounded bg-[#29b1dc] px-3 py-2 text-sm text-white hover:bg-[#24a8cf]">Actualizar</button>
            </div>
        </form>
    </div>
</x-layouts.app>