<x-layouts.app title="Nuevo Usuario">
    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Nuevo Usuario</h1>
            <a href="{{ route('users.index') }}"
               class="px-4 py-2 text-sm text-white bg-gray-500 hover:bg-gray-600 rounded transition">
                ← Volver
            </a>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm p-6">
            <form action="{{ route('users.store') }}" method="POST" id="user-form">
                @csrf
                @include('users._form', ['user' => null])
                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] transition">
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
    @include('users._form_scripts')
</x-layouts.app>