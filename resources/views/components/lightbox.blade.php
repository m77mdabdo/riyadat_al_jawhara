{{--
    Thumbnail grid + accessible full-screen viewer, shared by project and
    stone galleries.
    - Dialog semantics (role="dialog", aria-modal), focus moves into the
      dialog on open, Tab is trapped inside it, and focus returns to the
      thumbnail that opened it.
    - Escape closes; arrow keys / swipe navigate. In RTL the arrow keys are
      mirrored so "next" always follows reading direction.
    - Page scroll is locked while open. Only the active image is loaded.
    - layout="masonry" (gallery page): natural-aspect tiles in CSS columns,
      with real width/height so there is no layout shift. Without $alt, each
      item is described by its own project/stone name.
--}}
@props(['media', 'alt' => null, 'layout' => 'grid'])

@php
    // Prefer the WebP renditions, then the JPEG conversions, then the original.
    $pick = function ($item, array $names): string {
        foreach ($names as $name) {
            if ($item->hasGeneratedConversion($name)) {
                return $item->getUrl($name);
            }
        }

        return $item->getUrl();
    };

    $thumbConversion = function ($item): ?string {
        foreach (['card', 'thumb'] as $name) {
            if ($item->hasGeneratedConversion($name)) {
                return $name;
            }
        }

        return null;
    };

    $items = $media->values()->map(function ($item) use ($pick, $thumbConversion, $alt) {
        $conversion = $thumbConversion($item);
        $size = rescue(fn () => getimagesize($conversion ? $item->getPath($conversion) : $item->getPath()), null, false) ?: [4, 3];

        return [
            'thumb' => $pick($item, ['card', 'thumb']),
            'full' => $pick($item, ['large', 'medium']),
            'alt' => $alt ?? ($item->model?->title ?? $item->model?->name ?? ''),
            'width' => $size[0],
            'height' => $size[1],
        ];
    });
@endphp

<div
    x-data="{
        open: false,
        active: 0,
        items: @js($items),
        touchX: 0,
        opener: null,
        rtl: document.documentElement.dir === 'rtl',
        show(index, el) {
            this.active = index;
            this.opener = el;
            this.open = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.close.focus());
        },
        hide() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
            this.$nextTick(() => this.opener?.focus());
        },
        next() { this.active = (this.active + 1) % this.items.length },
        prev() { this.active = (this.active - 1 + this.items.length) % this.items.length },
        arrow(key) {
            const forward = key === 'ArrowRight' ? ! this.rtl : this.rtl;
            forward ? this.next() : this.prev();
        },
        trap(event) {
            const focusable = [...this.$refs.dialog.querySelectorAll('button')];
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (! event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        },
    }"
    {{ $attributes }}
>
    @if ($layout === 'masonry')
        <div class="columns-2 sm:columns-3 lg:columns-4 gap-3 sm:gap-4">
            @foreach ($items as $index => $item)
                <button type="button" @click="show({{ $index }}, $el)" class="group mb-3 sm:mb-4 block w-full break-inside-avoid overflow-hidden rounded-lg bg-stone-200" aria-label="{{ __('site.common.view_image') }} {{ $index + 1 }} / {{ $items->count() }}: {{ $item['alt'] }}">
                    <img src="{{ $item['thumb'] }}" alt="" width="{{ $item['width'] }}" height="{{ $item['height'] }}" loading="lazy" decoding="async" class="h-auto w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] transition-transform duration-700 group-hover:scale-105">
                </button>
            @endforeach
        </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($items as $index => $item)
            <button type="button" @click="show({{ $index }}, $el)" class="aspect-square rounded-lg overflow-hidden bg-stone-200" aria-label="{{ __('site.common.view_image') }} {{ $index + 1 }} / {{ $items->count() }}: {{ $item['alt'] }}">
                <img src="{{ $item['thumb'] }}" alt="" width="400" height="300" loading="lazy" decoding="async" class="h-full w-full object-cover saturate-[0.92] contrast-[1.03] sepia-[0.04] hover:scale-105 transition-transform duration-300">
            </button>
        @endforeach
    </div>
    @endif

    <div
        x-show="open"
        x-cloak
        x-ref="dialog"
        x-transition.opacity.duration.200ms
        role="dialog"
        aria-modal="true"
        aria-label="{{ $alt ?? __('site.projects.gallery') }}"
        class="fixed inset-0 z-[70] bg-charcoal/95 flex items-center justify-center p-4 sm:p-12"
        @click.self="hide()"
        @keydown.escape.window="open && hide()"
        @keydown.arrow-right.window="open && arrow('ArrowRight')"
        @keydown.arrow-left.window="open && arrow('ArrowLeft')"
        @keydown.tab="trap($event)"
        @touchstart.passive="touchX = $event.changedTouches[0].screenX"
        @touchend="const dx = $event.changedTouches[0].screenX - touchX; if (Math.abs(dx) > 40) { (dx < 0) !== rtl ? next() : prev() }"
    >
        <button type="button" x-ref="close" @click="hide()" class="absolute top-3 end-3 sm:top-6 sm:end-6 h-11 w-11 flex items-center justify-center rounded-full text-cream hover:bg-white/10" aria-label="{{ __('site.common.close') }}">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        @if ($items->count() > 1)
            <button type="button" @click.stop="prev()" class="absolute start-2 sm:start-4 top-1/2 -translate-y-1/2 h-11 w-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-cream" aria-label="{{ __('site.common.previous_image') }}">
                <svg class="h-6 w-6 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" @click.stop="next()" class="absolute end-2 sm:end-4 top-1/2 -translate-y-1/2 h-11 w-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-cream" aria-label="{{ __('site.common.next_image') }}">
                <svg class="h-6 w-6 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        @endif

        <template x-if="open">
            <img :src="items[active].full" :alt="items[active].alt" decoding="async" class="max-h-lightbox max-w-full rounded-lg object-contain saturate-[0.92] contrast-[1.03] sepia-[0.04]">
        </template>

        <p class="absolute bottom-4 inset-x-0 text-center text-sm text-stone-300" aria-live="polite"><bdi x-text="`${active + 1} / ${items.length}`"></bdi></p>
    </div>
</div>
