@if ($students->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 sm:gap-6">
        <!-- Legend -->
        <div class="text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
            Mostrando
            <span class="font-medium">{{ $students->firstItem() }}</span>
            a
            <span class="font-medium">{{ $students->lastItem() }}</span>
            de
            <span class="font-medium">{{ $students->total() }}</span>
        </div>

        <!-- Pagination links -->
        <div class="flex items-center">
            {!! $students->appends(request()->query())->links() !!}
        </div>
    </nav>
@endif