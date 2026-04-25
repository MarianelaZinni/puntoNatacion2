<x-layouts.app title="Comunicados">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <flux:icon name="megaphone" class="h-6 w-6 text-[#29b1dc]" />
                Comunicados
            </h1>
            <a href="{{ route('portal.student') }}" wire:navigate
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
                ← Volver al portal
            </a>
        </div>

        @if($announcements->isEmpty())
            <div class="bg-gray-50 dark:bg-zinc-900/40 border border-gray-200 dark:border-zinc-700 rounded-lg p-8 text-center text-gray-500 dark:text-gray-400">
                No hay comunicados publicados por el momento.
            </div>
        @else
            <div class="space-y-4">
                @foreach($announcements as $announcement)
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4 mb-2">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $announcement->title }}</h2>
                        @if($announcement->is_read)
                            <span class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                <flux:icon name="check-circle" class="h-3.5 w-3.5" />
                                Leído
                            </span>
                        @else
                            <span class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#29b1dc]/15 text-[#1a8eb5] dark:text-[#29b1dc]">
                                <flux:icon name="envelope" class="h-3.5 w-3.5" />
                                No leído
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $announcement->body }}</p>
                    <div class="flex items-center justify-between mt-3">
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $announcement->created_at->diffForHumans() }}</p>
                        @if(!$announcement->is_read)
                        <form action="{{ route('portal.announcements.read', $announcement) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border border-[#29b1dc]/50 text-[#29b1dc] hover:bg-[#29b1dc] hover:text-white dark:hover:bg-[#29b1dc] transition">
                                <flux:icon name="check" class="h-3.5 w-3.5" />
                                Marcar como leído
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
