<x-filament-widgets::widget>
    <x-filament::section :heading="__('Quick actions')">
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
            @foreach ($this->actions() as $action)
                <x-filament::button tag="a" :href="$action['url']" :icon="$action['icon']" color="gray" size="sm"
                    :target="str_starts_with($action['url'], url('/admin')) ? null : '_blank'">
                    {{ $action['label'] }}
                </x-filament::button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
