@if ($paginator->hasPages())
<nav class="pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-left"></i></span></span>
    @else
        <a class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a></a>
    @endif

    {{-- Pages --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="page-item disabled"><span class="page-link">…</span></span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-item active"><span class="page-link">{{ $page }}</span></span>
                @else
                    <a class="page-item" href="{{ $url }}"><span class="page-link">{{ $page }}</span></a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a class="page-item" href="{{ $paginator->nextPageUrl() }}" rel="next"><span class="page-link"><i class="bi bi-chevron-right"></i></span></a>
    @else
        <span class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-right"></i></span></span>
    @endif
</nav>
@endif
