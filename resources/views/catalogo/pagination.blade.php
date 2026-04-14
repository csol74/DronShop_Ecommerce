@if ($paginator->hasPages())
    <nav class="ds-pagination">
        @if ($paginator->onFirstPage())
            <span>‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">‹</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span>{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="background:var(--gold-500);border-color:var(--gold-500);
                                     color:#0a0a0a;font-weight:700;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">›</a>
        @else
            <span>›</span>
        @endif
    </nav>
@endif
