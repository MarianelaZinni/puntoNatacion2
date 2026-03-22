<x-layouts.app title="Métodos de pago">
    <div class="max-w-6xl mx-auto py-8 px-4">
       <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">MÉTODOS DE PAGO</h1>

            <a href="{{ route('payment_methods.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                 <flux:icon name="user-plus" class="h-5 w-5" />
                Nuevo Método de Pago
            </a>
        </div>
 @if(session('success'))
    <div id="flash-success" class="mb-4 p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/30 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-3 shadow-sm"
         role="status" aria-live="polite" data-timeout="5000">
        <div class="flex-1">
            {{ session('success') }}
        </div>

        <!-- Close button -->
        <button type="button"
                class="ml-2 -mr-1 p-1 rounded hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-green-800 dark:text-green-200"
                aria-label="Cerrar mensaje" id="flash-success-close">
            <!-- simple X icon -->
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

       @if(session('error'))
    <div id="flash-error"
         class="mb-4 p-3 rounded border border-red-200 bg-red-50 dark:bg-red-900/30 dark:border-red-800 text-red-800 dark:text-red-200 flex items-start gap-3 shadow-sm"
         role="alert" aria-live="assertive" data-timeout="8000">
        <div class="flex-1">
            {{ session('error') }}
        </div>

        <!-- Close button -->
        <button type="button"
                class="ml-2 -mr-1 p-1 rounded hover:bg-red-100 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-red-800 dark:text-red-200"
                aria-label="Cerrar mensaje" id="flash-error-close">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif
    
        <div id="payment-methods-table-wrapper" class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
    <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="id" type="button" aria-sort="none">
        ID
        <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="id"></span>
    </button>
</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
    <button class="sort-btn cursor-pointer focus:outline-none flex items-center gap-2" data-sort="name" type="button" aria-sort="none">
        Descripción
        <span class="sort-indicator text-gray-400 dark:text-gray-500 text-xs" data-field="name"></span>
    </button>
</th>
             <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>

                <tbody id="payment-methods-body" class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                    @include('payment_methods.partials.rows', ['paymentMethods' => $paymentMethods])
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        /**
         * Global confirmDelete function used by the delete buttons:
         * <button onclick="confirmDelete(this)">...
         *
         * Finds the closest form and submits it after confirmation.
         */
        window.confirmDelete = function (btn) {
            const methodName = btn.getAttribute('data-method-name') || 'este método de pago';
            // Safety: ensure Swal is available
            if (typeof Swal === 'undefined') {
                // fallback browser confirm
                const form = btn.closest('form');
                if (typeof Swal !== 'undefined') {
    Swal.fire({
        title: '¿Eliminar este método de pago?',
        html: `<div class="text-left">
            <p class="mb-2">Método: <strong>${methodName}</strong></p>
            <p class="mt-3 text-sm text-gray-600">Esta acción no se puede deshacer.</p>
        </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e53e3e',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
} else {
    // Fallback to native confirm
    if (confirm('¿Estás seguro? Esta acción no se puede deshacer.')) {
        form.submit();
    }
}
                return;
            }

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡Esta acción no se puede deshacer!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = btn.closest('form');
                    if (form) form.submit();
                }
            });
        };

        /**
         * Optional: confirm before navigating to "Editar".
         * We detect anchors whose aria-label starts with "Editar" and intercept clicks.
         * If you don't want confirmation for edit, you can remove this block.
         */
        document.addEventListener('DOMContentLoaded', function () {
            // attach confirm to any edit links with aria-label starting with "Editar"
            const editLinks = Array.from(document.querySelectorAll('a[aria-label^="Editar"]'));
            editLinks.forEach(a => {
                // Avoid attaching multiple handlers
                if (a.__confirmEditAttached) return;
                a.__confirmEditAttached = true;

                a.addEventListener('click', function (e) {
                    // If user holds meta/ctrl to open in new tab, skip confirmation
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                    e.preventDefault();
                    const href = a.href;

                    // If you prefer direct navigation without confirmation, remove the Swal block.
                    Swal.fire({
                        title: 'Editar elemento',
                        text: "¿Querés editar este método de pago?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#29b1dc',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, editar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // navigate to edit page
                            window.location.href = href;
                        }
                    });
                });
            });
        });
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.getElementById('flash-success');
    if (!alert) return;

    // lee timeout desde data attribute (ms)
    const timeout = parseInt(alert.dataset.timeout || 5000, 10);

    // helper: fade out and remove
    const dismissAlert = () => {
        alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        // after transition remove from DOM
        setTimeout(() => {
            if (alert && alert.parentNode) alert.parentNode.removeChild(alert);
        }, 500);
    };

    // auto dismiss
    const timer = setTimeout(dismissAlert, timeout);

    // close button
    const closeBtn = document.getElementById('flash-success-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            clearTimeout(timer);
            dismissAlert();
        });
    }

    // if user focuses inside alert (keyboard), keep it visible — optional
    alert.addEventListener('focusin', function () {
        clearTimeout(timer);
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Reusable handler for flash alerts
    function initFlash(id, closeId) {
        const el = document.getElementById(id);
        if (!el) return;

        const timeout = parseInt(el.dataset.timeout || 5000, 10);

        const dismiss = () => {
            el.classList.add('opacity-0', 'transition-opacity', 'duration-500');
            setTimeout(() => {
                if (el && el.parentNode) el.parentNode.removeChild(el);
            }, 500);
        };

        const timer = setTimeout(dismiss, timeout);

        const closeBtn = document.getElementById(closeId);
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                clearTimeout(timer);
                dismiss();
            });
        }

        // If user focuses inside alert (keyboard), keep it visible
        el.addEventListener('focusin', function () {
            clearTimeout(timer);
        });
    }

    // initialize both success and error if present
    initFlash('flash-success', 'flash-success-close');
    initFlash('flash-error', 'flash-error-close');
});
</script>
    @endpush
</x-layouts.app>