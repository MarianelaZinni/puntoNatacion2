<x-layouts.app title="Nuevo Comunicado">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Nuevo Comunicado</h1>
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('announcements.store') }}" method="POST"
              class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm space-y-6">
            @csrf

            {{-- Título --}}
            <div>
                <label for="title" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" name="title" required maxlength="255"
                       value="{{ old('title') }}"
                       class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50">
                @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cuerpo --}}
            <div>
                <label for="body" class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Contenido <span class="text-red-500">*</span>
                </label>
                <textarea id="body" name="body" rows="7" required
                          class="block w-full rounded-md border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#29b1dc] focus:ring focus:ring-[#29b1dc] focus:ring-opacity-50"
                          placeholder="Escribí el contenido del comunicado...">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Estado --}}
            <div class="flex items-center gap-3">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', '1') ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 dark:border-zinc-600 text-[#29b1dc] focus:ring-[#29b1dc]">
                <label for="is_active" class="text-base text-gray-700 dark:text-gray-300">
                    Publicado (visible para los alumnos)
                </label>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('announcements.index') }}"
                   class="px-5 py-2 rounded text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 focus:outline-none transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                    Crear Comunicado
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
