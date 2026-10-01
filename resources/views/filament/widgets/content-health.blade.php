<x-filament-widgets::widget>
    <x-filament::section :heading="__('Content health')" :description="__('Things worth fixing on the website. Updated every few minutes.')" collapsible>
        @php($problems = $this->problems())
        @if ($problems === [])
            <p style="display:flex;align-items:center;gap:0.5rem">
                <x-filament::icon icon="heroicon-o-check-circle" style="width:1.25rem;height:1.25rem;color:rgb(22 163 74)" />
                {{ __('Everything looks complete.') }}
            </p>
        @else
            <ul style="display:grid;gap:0.5rem">
                @foreach ($problems as $problem)
                    <li style="display:flex;align-items:center;justify-content:space-between;gap:1rem">
                        <span style="display:flex;align-items:center;gap:0.5rem">
                            <x-filament::badge color="warning">{{ $problem['count'] }}</x-filament::badge>
                            {{ __('admin.content_health.'.$problem['key']) }}
                        </span>
                        <x-filament::link :href="$problem['url']" icon="heroicon-m-arrow-small-right" icon-position="after">
                            {{ __('Fix') }}
                        </x-filament::link>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
