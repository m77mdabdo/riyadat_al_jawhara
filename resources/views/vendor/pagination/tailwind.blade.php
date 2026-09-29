{{--
    Site pagination (overrides Laravel's default Tailwind view):
    brand colours, no dark: variants (the site has no dark theme), logical
    spacing so it mirrors in RTL, 44px touch targets, translated labels.
--}}
@if ($paginator->hasPages())
    @php
        $base = 'inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border px-3 text-sm font-medium transition-colors';
        $idle = $base.' border-stone-300 bg-white text-charcoal hover:bg-stone-100';
        $disabled = $base.' border-stone-200 bg-stone-50 text-stone-400 cursor-default';
        $current = $base.' border-charcoal bg-charcoal text-cream';
    @endphp

    <nav role="navigation" aria-label="{{ __('site.pagination.label') }}" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <p class="text-sm text-stone-600 order-2 sm:order-1">
            {{ __('site.pagination.summary', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $paginator->total()]) }}
        </p>

        <ul class="flex flex-wrap items-center justify-center gap-2 order-1 sm:order-2">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $disabled }}" aria-disabled="true">
                        <span class="sr-only">{{ trim(html_entity_decode(__('pagination.previous')), ' «»') }}</span>
                        <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $idle }}">
                        <span class="sr-only">{{ trim(html_entity_decode(__('pagination.previous')), ' «»') }}</span>
                        <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="{{ $disabled }}" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block @if ($page === $paginator->currentPage()) !block @endif">
                            @if ($page === $paginator->currentPage())
                                <span class="{{ $current }}" aria-current="page"><bdi>{{ $page }}</bdi></span>
                            @else
                                <a href="{{ $url }}" class="{{ $idle }}" aria-label="{{ __('site.pagination.go_to_page', ['page' => $page]) }}"><bdi>{{ $page }}</bdi></a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $idle }}">
                        <span class="sr-only">{{ trim(html_entity_decode(__('pagination.next')), ' «»') }}</span>
                        <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="{{ $disabled }}" aria-disabled="true">
                        <span class="sr-only">{{ trim(html_entity_decode(__('pagination.next')), ' «»') }}</span>
                        <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
