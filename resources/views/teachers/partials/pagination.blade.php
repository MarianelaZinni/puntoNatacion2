@if ($teachers->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 sm:gap-6">
        <div class="text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
            Mostrando
            <span class="font-medium">{{ $teachers->firstItem() }}</span>
            a
            <span class="font-medium">{{ $teachers->lastItem() }}</span>
            de
            <span class="font-medium">{{ $teachers->total() }}</span>
        </div>

        <div class="flex items-center">
            {!! $teachers->appends(request()->query())->links() !!}
        </div>
    </nav>
@endif
