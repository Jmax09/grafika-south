@if ($paginator->hasPages())
    {{-- Grafika South branded pagination: inline styles (no Tailwind dependency).
         Pass hide-info="1" to links() when the page already renders its own counter. --}}
    @php $hideInfo = isset($hideInfo) && $hideInfo; @endphp
    <nav role="navigation" aria-label="Pagination Navigation" style="display: flex; align-items: center; justify-content: {{ $hideInfo ? 'flex-end' : 'space-between' }}; gap: 0.75rem; flex-wrap: wrap;">

        @unless ($hideInfo)
            <p style="margin: 0; font-size: 0.78rem; color: #64748B;">
                Showing
                @if ($paginator->firstItem())
                    <strong style="color: #0F172A;">{{ $paginator->firstItem() }}</strong>
                    to
                    <strong style="color: #0F172A;">{{ $paginator->lastItem() }}</strong>
                @else
                    <strong style="color: #0F172A;">{{ $paginator->count() }}</strong>
                @endif
                of <strong style="color: #0F172A;">{{ $paginator->total() }}</strong> results
            </p>
        @endunless

        <div style="display: inline-flex; align-items: center; gap: 0.3rem;">

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Previous"
                      style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border: 1px solid #E2E8F0; border-radius: 8px; background: #FFF; color: #CBD5E1; cursor: not-allowed; font-size: 0.8rem;">
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous"
                   onmouseover="this.style.borderColor='#0099FF'; this.style.color='#0099FF';" onmouseout="this.style.borderColor='#E2E8F0'; this.style.color='#475569';"
                   style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border: 1px solid #E2E8F0; border-radius: 8px; background: #FFF; color: #475569; text-decoration: none; font-size: 0.8rem; transition: all 0.15s ease;">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true"
                          style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; color: #94A3B8; font-size: 0.82rem; cursor: default;">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  style="display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 0.4rem; border-radius: 8px; background: #0099FF; color: #FFFFFF; font-weight: 700; font-size: 0.84rem;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Go to page {{ $page }}"
                               onmouseover="this.style.background='#EFF6FF'; this.style.color='#0099FF';" onmouseout="this.style.background='#FFF'; this.style.color='#475569';"
                               style="display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 0.4rem; border: 1px solid #E2E8F0; border-radius: 8px; background: #FFF; color: #475569; font-weight: 600; font-size: 0.84rem; text-decoration: none; box-sizing: border-box; transition: all 0.15s ease;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next"
                   onmouseover="this.style.borderColor='#0099FF'; this.style.color='#0099FF';" onmouseout="this.style.borderColor='#E2E8F0'; this.style.color='#475569';"
                   style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border: 1px solid #E2E8F0; border-radius: 8px; background: #FFF; color: #475569; text-decoration: none; font-size: 0.8rem; transition: all 0.15s ease;">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span aria-disabled="true" aria-label="Next"
                      style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border: 1px solid #E2E8F0; border-radius: 8px; background: #FFF; color: #CBD5E1; cursor: not-allowed; font-size: 0.8rem;">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
