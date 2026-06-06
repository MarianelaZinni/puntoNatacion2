<x-layouts.app title="Plan mensual">
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Plan mensual</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $subject->subjectType?->description ?? 'Clase' }} · {{ $subject->day }} {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('portal.teacher') }}" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Volver</a>
                @if ($canManage)
                    <a href="{{ route('portal.teacher.plans.create', $subject) }}" class="rounded bg-[#29b1dc] px-3 py-2 text-sm text-white hover:bg-[#24a8cf]">
                        Nuevo plan
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse ($plans as $plan)
                <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h2 class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ $plan->month->translatedFormat('F Y') }}
                        </h2>
                        @if ($canManage)
                            <div class="flex gap-2">
                                <a href="{{ route('portal.teacher.plans.edit', [$subject, $plan]) }}" class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Editar</a>
                                <form action="{{ route('portal.teacher.plans.destroy', [$subject, $plan]) }}" method="POST" onsubmit="return confirm('¿Eliminar este plan mensual?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded bg-red-500 px-2 py-1 text-xs text-white hover:bg-red-600">Eliminar</button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $plan->content }}</p>
                </article>
            @empty
                <div class="rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-gray-400">
                    No hay planes cargados para esta clase.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
