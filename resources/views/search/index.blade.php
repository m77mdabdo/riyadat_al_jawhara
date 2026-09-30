<x-layout :title="$term !== '' ? __('site.search.title').': '.$term : __('site.search.title')" :description="__('site.search.subtitle')" robots="noindex, follow">
    <x-page-header :title="__('site.search.title')" :subtitle="__('site.search.subtitle')" texture="texture-quartz">
        <form method="GET" action="{{ route('search') }}" role="search" class="mt-8 mx-auto flex max-w-xl flex-col gap-3 sm:flex-row">
            <label for="site-search" class="sr-only">{{ __('site.search.label') }}</label>
            <input id="site-search" type="search" name="q" value="{{ $term }}" minlength="2" maxlength="100" required autofocus
                   placeholder="{{ __('site.search.placeholder') }}"
                   class="min-h-11 w-full flex-1 rounded-md px-4 py-2.5 text-base text-charcoal focus:border-charcoal focus:ring-charcoal">
            <x-button type="submit" class="w-full sm:w-auto">
                <x-heroicon-o-magnifying-glass class="h-4 w-4" aria-hidden="true" /> {{ __('site.search.button') }}
            </x-button>
        </form>
    </x-page-header>

    <section class="py-12 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($term === '')
            <p class="text-center text-stone-600">{{ __('site.search.hint') }}</p>
        @elseif (mb_strlen($term) < 2)
            <p class="text-center text-stone-600">{{ __('site.search.too_short') }}</p>
        @else
            <p class="mb-6 text-stone-700" role="status">{{ trans_choice('site.search.results', $results->total(), ['count' => $results->total(), 'term' => $term]) }}</p>

            @if ($results->isEmpty())
                <p class="text-stone-600">{{ __('site.search.hint') }}</p>
            @else
                <ul class="space-y-4">
                    @foreach ($results as $result)
                        <li>
                            <a href="{{ $result['url'] }}" class="group block rounded-xl border border-stone-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-sand-300 hover:shadow-md">
                                <span class="inline-flex rounded-full bg-sand-100 px-3 py-1 text-sm sm:text-xs font-semibold text-sand-700">{{ __('site.search.types.'.$result['type']) }}</span>
                                <span class="mt-2 block font-semibold text-charcoal break-words"><span class="link-sweep">{{ $result['title'] }}</span></span>
                                @if ($result['excerpt'] !== '')
                                    <span class="mt-1 block text-sm text-stone-600 break-words">{{ $result['excerpt'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-10">{{ $results->links('pagination::tailwind') }}</div>
            @endif
        @endif
    </section>
</x-layout>
