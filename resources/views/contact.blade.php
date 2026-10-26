<x-layout :title="__('site.contact.title')" :description="__('site.contact.subtitle')" :breadcrumbs="[['name' => __('site.contact.title'), 'url' => route('contact')]]">
    <x-page-header :title="__('site.contact.title')" :subtitle="__('site.contact.subtitle')" texture="texture-sandstone" />

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-5 gap-12">
        <div class="lg:col-span-3">
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-5 py-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-5 py-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-5 bg-white border border-stone-200 rounded-xl p-6 sm:p-8" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                {{-- Honeypot: same approach as the blog comment form -- clipped to a 1x1px box in
                     normal flow, hidden from assistive tech, out of the tab order, no autocomplete. --}}
                <div class="h-px w-px overflow-hidden" aria-hidden="true">
                    <label for="website-contact">Website</label>
                    <input type="text" name="website" id="website-contact" tabindex="-1" autocomplete="off">
                </div>

                @if ($selectedProject)
                    <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                    <p class="rounded-md bg-stone-100 px-4 py-3 text-sm text-stone-700">
                        {{ __('site.contact.regarding_project') }} <span class="font-semibold text-charcoal">{{ $selectedProject->title }}</span>
                    </p>
                @elseif (old('project_id'))
                    <input type="hidden" name="project_id" value="{{ old('project_id') }}">
                @endif

                <div>
                    <label for="name" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_name') }}</label>
                    <input type="text" name="name" id="name" autocomplete="name" value="{{ old('name') }}" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror class="w-full min-h-11 rounded-md px-3 py-2.5 text-base focus:border-charcoal focus:ring-charcoal @error('name') border-red-400 @else border-stone-300 @enderror">
                    @error('name') <p id="name-error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_phone') }}</label>
                        <input type="tel" name="phone" id="phone" inputmode="tel" autocomplete="tel" dir="ltr" aria-describedby="phone-hint @error('phone') phone-error @enderror" @error('phone') aria-invalid="true" @enderror value="{{ old('phone') }}" class="w-full min-h-11 rounded-md px-3 py-2.5 text-base focus:border-charcoal focus:ring-charcoal @error('phone') border-red-400 @else border-stone-300 @enderror">
                        <p id="phone-hint" class="mt-1 text-sm sm:text-xs text-stone-600">{{ __('site.contact.phone_hint') }} <bdi dir="ltr">+966 5X XXX XXXX</bdi></p>
                        @error('phone') <p id="phone-error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_email') }}</label>
                        <input type="email" name="email" id="email" autocomplete="email" dir="ltr" value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="w-full min-h-11 rounded-md px-3 py-2.5 text-base focus:border-charcoal focus:ring-charcoal @error('email') border-red-400 @else border-stone-300 @enderror">
                        @error('email') <p id="email-error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="city" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_city') }}</label>
                        <input type="text" name="city" id="city" autocomplete="address-level2" value="{{ old('city') }}" @error('city') aria-invalid="true" aria-describedby="city-error" @enderror class="w-full min-h-11 rounded-md px-3 py-2.5 text-base focus:border-charcoal focus:ring-charcoal @error('city') border-red-400 @else border-stone-300 @enderror">
                        @error('city') <p id="city-error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="service_id" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_service') }}</label>
                        <select name="service_id" id="service_id" @error('service_id') aria-invalid="true" aria-describedby="service_id-error" @enderror class="w-full min-h-11 rounded-md px-3 py-2.5 text-base focus:border-charcoal focus:ring-charcoal @error('service_id') border-red-400 @else border-stone-300 @enderror">
                            <option value="">{{ __('site.contact.choose_service') }}</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id', $selectedServiceId) == $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('service_id') <p id="service_id-error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="stone_type_id" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_stone_type') }}</label>
                    <select name="stone_type_id" id="stone_type_id" @error('stone_type_id') aria-invalid="true" aria-describedby="stone_type_id-error" @enderror class="w-full min-h-11 rounded-md px-3 py-2.5 text-base focus:border-charcoal focus:ring-charcoal @error('stone_type_id') border-red-400 @else border-stone-300 @enderror">
                        <option value="">{{ __('site.contact.choose_stone_type') }}</option>
                        @foreach ($stoneTypes as $stoneType)
                            <option value="{{ $stoneType->id }}" @selected(old('stone_type_id', $selectedStoneTypeId) == $stoneType->id)>{{ $stoneType->name }}</option>
                        @endforeach
                    </select>
                    @error('stone_type_id') <p id="stone_type_id-error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_message') }}</label>
                    <textarea name="message" id="message" rows="4" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror class="w-full min-h-11 rounded-md px-3 py-2.5 text-base focus:border-charcoal focus:ring-charcoal @error('message') border-red-400 @else border-stone-300 @enderror">{{ old('message') }}</textarea>
                    @error('message') <p id="message-error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="consent" class="flex min-h-11 items-start gap-3 text-sm text-stone-700 leading-relaxed rtl:leading-loose cursor-pointer">
                        <input type="checkbox" name="consent" id="consent" value="1" @checked(old('consent')) @error('consent') aria-invalid="true" aria-describedby="consent-error" @enderror class="mt-1 h-5 w-5 shrink-0 rounded text-charcoal focus:ring-charcoal @error('consent') border-red-400 @else border-stone-300 @enderror">
                        <span>{!! __('site.contact.consent_label', ['link' => '<a href="'.e(route('legal.privacy')).'" target="_blank" class="font-semibold text-charcoal underline underline-offset-2 hover:no-underline">'.e(__('site.legal.privacy_policy_title')).'</a>']) !!}</span>
                    </label>
                    @error('consent') <p id="consent-error" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <x-button type="submit" x-bind:disabled="submitting" class="w-full sm:w-auto disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg x-show="submitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? @js(__('site.common.sending')) : @js(__('site.common.send_message'))">{{ __('site.common.send_message') }}</span>
                </x-button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-stone-200 bg-white p-6">
                <h2 class="text-xl font-bold text-charcoal mb-4">{{ __('site.contact.get_in_touch') }}</h2>
                <ul class="space-y-3 text-sm text-stone-600">
                    @if ($settings->phone)
                        <li><a href="{{ $settings->phone_href }}" class="inline-flex min-h-11 items-center gap-2 hover:text-charcoal"><x-heroicon-o-phone class="h-4 w-4 text-charcoal shrink-0" /> <bdi dir="ltr">{{ $settings->phone }}</bdi></a></li>
                    @endif
                    @if ($settings->email)
                        <li><a href="mailto:{{ $settings->email }}" class="inline-flex min-h-11 items-center gap-2 break-all hover:text-charcoal"><x-heroicon-o-envelope class="h-4 w-4 text-charcoal shrink-0" /> <bdi>{{ $settings->email }}</bdi></a></li>
                    @endif
                    @if ($settings->address)
                        <li class="flex items-center gap-2"><x-heroicon-o-map-pin class="h-4 w-4 text-charcoal shrink-0" /> {{ $settings->address }}</li>
                    @endif
                    @if ($settings->working_hours)
                        <li class="flex items-center gap-2"><x-heroicon-o-clock class="h-4 w-4 text-charcoal shrink-0" /> <bdi>{{ $settings->working_hours }}</bdi></li>
                    @endif
                </ul>
                @if ($settings->whatsapp_url)
                    <a data-wa-track href="{{ $settings->whatsapp_url }}" target="_blank" rel="noopener" class="mt-6 flex items-center justify-center gap-2 rounded-md bg-green-700 hover:bg-green-800 transition-colors text-white text-sm font-semibold px-5 py-3">
                        <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" /> {{ __('site.common.whatsapp_us') }}
                    </a>
                @endif
            </div>

            @if ($settings->map_lat && $settings->map_lng)
                <div class="rounded-xl overflow-hidden border border-stone-200 aspect-video">
                    <iframe
                        class="w-full h-full"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="{{ __('site.contact.our_location') }}"
                        allowfullscreen
                        src="https://maps.google.com/maps?q={{ $settings->map_lat }},{{ $settings->map_lng }}&z=14&output=embed">
                    </iframe>
                </div>
                <a
                    href="https://www.google.com/maps/dir/?api=1&destination={{ $settings->map_lat }},{{ $settings->map_lng }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center justify-center gap-2 rounded-md border border-stone-300 hover:bg-stone-100 transition-colors text-charcoal text-sm font-semibold px-5 py-3"
                >
                    <x-heroicon-o-map class="h-4 w-4" /> {{ __('site.contact.get_directions') }}
                </a>
            @endif
        </div>
    </section>
</x-layout>
