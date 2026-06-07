<x-layouts.app title="Notas de profesores">
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-3">
            <h1 class="flex items-center gap-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                <flux:icon name="chat-bubble-left-right" class="h-6 w-6 text-[#29b1dc]" />
                Notas de profesores
            </h1>
            <a href="{{ route('portal.student') }}" class="text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                ← Volver al portal
            </a>
        </div>

        @if ($notes->isEmpty())
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-8 text-center text-gray-500 dark:border-zinc-700 dark:bg-zinc-900/40 dark:text-gray-400">
                No hay notas disponibles.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($notes as $note)
                    <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="mb-2 flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <h2 class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $note->title ?: 'Nota de clase' }}
                                </h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Alumno: {{ $note->student?->name ?? '—' }}
                                    · Clase: {{ $note->subject?->subjectType?->description ?? '—' }}
                                    · {{ $note->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @if ($note->is_read)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                    <flux:icon name="check-circle" class="h-3.5 w-3.5" />
                                    Leído
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#29b1dc]/15 px-2.5 py-0.5 text-xs font-medium text-[#1a8eb5] dark:text-[#29b1dc]">
                                    No leído
                                </span>
                            @endif
                        </div>
                        <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $note->body }}</p>

                        @if (! $note->is_read)
                            <form action="{{ route('portal.notes.read', $note) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-[#29b1dc]/50 px-3 py-1.5 text-xs font-medium text-[#29b1dc] transition hover:bg-[#29b1dc] hover:text-white">
                                    <flux:icon name="check" class="h-3.5 w-3.5" />
                                    Marcar como leída
                                </button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
