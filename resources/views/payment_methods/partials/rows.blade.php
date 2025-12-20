@forelse($paymentMethods as $paymentMethod)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $paymentMethod->id }}</td>
    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $paymentMethod->name }}</td>

    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
        <div class="flex flex-wrap items-center justify-center gap-2">

            <!-- Editar (pencil-square) -->
            <a href="{{ route('payment_methods.edit', $paymentMethod) }}"
               title="Editar {{ $paymentMethod->name }}"
               aria-label="Editar {{ $paymentMethod->name }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-yellow-100 dark:hover:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                <span class="sr-only">Editar</span>

                <flux:icon name="pencil-square" class="h-5 w-5" />
            </a>

             <!-- Eliminar (user-minus) - form required for DELETE -->
            <form action="{{ route('payment_methods.destroy', $paymentMethod) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="button"
                        onclick="confirmDelete(this)"
                        title="Eliminar {{ $paymentMethod->name }}"
                        aria-label="Eliminar {{ $paymentMethod->name }}"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-transparent hover:bg-red-100 dark:hover:bg-red-900 text-red-600 dark:text-red-400 border-0">
                    <span class="sr-only">Eliminar</span>

                    <flux:icon name="x-circle" class="h-5 w-5" />
                </button>
            </form>

        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="px-4 py-6 text-center text-gray-600 dark:text-gray-400">
        No hay tipos de pago cargados.
    </td>
</tr>
@endforelse