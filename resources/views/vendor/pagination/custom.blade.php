@if ($paginator->hasPages())
    <nav style="display: flex; gap: 6px; align-items: center;">
        @if ($paginator->onFirstPage())
            <span class="button button-secondary"
                style="opacity: 0.4; cursor: not-allowed; min-width: auto; padding: 10px 14px;">&laquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="button button-secondary"
                style="min-width: auto; padding: 10px 14px;">&laquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding: 10px 6px; color: #94a3b8;">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="button button-primary"
                            style="min-width: auto; padding: 10px 14px;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="button button-secondary"
                            style="min-width: auto; padding: 10px 14px;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="button button-secondary"
                style="min-width: auto; padding: 10px 14px;">&raquo;</a>
        @else
            <span class="button button-secondary"
                style="opacity: 0.4; cursor: not-allowed; min-width: auto; padding: 10px 14px;">&raquo;</span>
        @endif
    </nav>
@endif
