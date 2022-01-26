<section style="padding: 0 15px;">
        <p class="float-left text-bold-600" style="margin: 1rem">Showing <span class="text-danger">{{ $paginator->firstItem() }}</span> to <span class="text-danger">{{ $paginator->lastItem() }}</span> of <span class="text-danger">{{ $paginator->total() }}</span> records matching your search</p>
        @if ($paginator->hasPages())
        <ul class="pagination float-right" role="navigation">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li class="page-item">
                    <button class="page-link" onclick="EzwashHelper.searchCustomers(event,'{{ $paginator->previousPageUrl() }}')" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</button>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><button class="page-link" onclick="EzwashHelper.searchCustomers(event,'{{ $url }}')">{{ $page }}</button></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <button class="page-link" onclick="EzwashHelper.searchCustomers(event,'{{ $paginator->nextPageUrl() }}')" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</button>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
        @endif
</section>
