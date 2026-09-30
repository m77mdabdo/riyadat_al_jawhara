{{--
    PDPL consent banner. Rendered only when an analytics provider is
    configured (config/analytics.php). Nothing third-party loads until
    "Accept"; the choice is remembered in this browser and can be changed
    from the footer ("Cookie settings").
--}}
<div
    x-data="{
        open: false,
        read() { try { return JSON.parse(localStorage.getItem('jra_consent') || 'null') } catch (e) { return null } },
        save(choice) {
            try { localStorage.setItem('jra_consent', JSON.stringify({ v: 1, choice, at: new Date().toISOString() })) } catch (e) {}
            this.open = false;
            window.dispatchEvent(new CustomEvent('jra:consent', { detail: { choice } }));
        },
        init() {
            this.open = ! this.read();
            this.$watch('open', (value) => this.$nextTick(() => document.documentElement.style.setProperty('--consent-h', value ? this.$el.offsetHeight + 'px' : '0px')));
            if (this.open) this.$nextTick(() => document.documentElement.style.setProperty('--consent-h', this.$el.offsetHeight + 'px'));
        },
    }"
    @jra:consent-open.window="open = true"
    x-show="open"
    x-cloak
    role="region"
    aria-label="{{ __('site.consent.title') }}"
    class="fixed inset-x-0 bottom-0 z-[60] border-t border-sand-300 bg-cream shadow-2xl"
    style="padding-bottom: env(safe-area-inset-bottom);"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col lg:flex-row lg:items-center gap-4">
        <div class="flex-1 text-sm text-stone-700 leading-relaxed rtl:leading-loose">
            <p class="font-semibold text-charcoal">{{ __('site.consent.title') }}</p>
            <p class="mt-1">
                {{ __('site.consent.message') }}
                <a href="{{ route('legal.privacy') }}" class="font-semibold text-charcoal underline underline-offset-2">{{ __('site.legal.privacy_policy_title') }}</a>
            </p>
        </div>
        <div class="flex flex-wrap gap-3 shrink-0">
            <button type="button" @click="save('denied')" class="inline-flex min-h-11 items-center justify-center rounded-md border border-charcoal px-5 text-sm font-semibold text-charcoal hover:bg-stone-100">{{ __('site.consent.decline') }}</button>
            <button type="button" @click="save('granted')" class="inline-flex min-h-11 items-center justify-center rounded-md bg-charcoal px-5 text-sm font-semibold text-cream hover:bg-charcoal-700">{{ __('site.consent.accept') }}</button>
        </div>
    </div>
</div>
