<x-layouts.app title="Editar plan mensual">
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Editar plan mensual</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $subject->subjectType?->description ?? 'Clase' }} · {{ $subject->day }} {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
            </p>
        </div>

        <form action="{{ route('portal.teacher.plans.update', [$subject, $plan]) }}" method="POST" class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            @csrf
            @method('PUT')

            <div>
                <label for="month" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Mes</label>
                <input type="month" id="month" name="month" value="{{ old('month', $plan->month?->format('Y-m')) }}" required class="w-full rounded border-gray-300 bg-white text-gray-900 focus:ring-[#29b1dc] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100">
                @error('month') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="content" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Contenido del plan</label>
                <textarea id="content" name="content" rows="10" required class="w-full rounded border-gray-300 bg-white text-gray-900 focus:ring-[#29b1dc] dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-100">{{ old('content', $plan->content) }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <a href="{{ route('portal.teacher.plans.index', $subject) }}" class="rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Cancelar</a>
                <button type="submit" class="rounded bg-[#29b1dc] px-3 py-2 text-sm text-white hover:bg-[#24a8cf]">Actualizar</button>
            </div>
        </form>
    </div>
</x-layouts.app>

