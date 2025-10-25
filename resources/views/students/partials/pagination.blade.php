@if ($students->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
         {!! $students->appends(request()->query())->links() !!}
    </nav>
@endif

