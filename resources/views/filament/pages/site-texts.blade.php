<x-filament-panels::page>
    {{ $this->filtersForm }}

    @php($total = count($this->matchingKeys()))

    @if ($total === 0)
        <x-filament::section>
            <p>{{ __('No texts match the search.') }}</p>
        </x-filament::section>
    @else
        <form wire:submit="save" class="grid gap-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <x-filament::button color="gray" icon="heroicon-m-chevron-left" :disabled="$page <= 1" wire:click="goTo({{ $page - 1 }})" type="button">
                        {{ __('Previous') }}
                    </x-filament::button>
                    <span class="text-sm">{{ __('Page :page of :pages (:total texts)', ['page' => $page, 'pages' => $this->lastPage(), 'total' => $total]) }}</span>
                    <x-filament::button color="gray" icon="heroicon-m-chevron-right" icon-position="after" :disabled="$page >= $this->lastPage()" wire:click="goTo({{ $page + 1 }})" type="button">
                        {{ __('Next') }}
                    </x-filament::button>
                </div>
                <x-filament::button type="submit">
                    {{ __('Save texts on this page') }}
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>
