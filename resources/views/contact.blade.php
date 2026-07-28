<x-layout :title="__('site.contact.title')" :description="__('site.contact.subtitle')">
    <section class="py-16 bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-charcoal">{{ __('site.contact.title') }}</h1>
            <p class="mt-3 text-stone-600">{{ __('site.contact.subtitle') }}</p>
        </div>
    </section>

    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-5 gap-12">
        <div class="lg:col-span-3">
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-5 py-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-5 bg-white border border-stone-200 rounded-xl p-6 sm:p-8">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full rounded-md border-stone-300 focus:border-charcoal focus:ring-charcoal @error('name') border-red-400 @enderror">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_phone') }}</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full rounded-md border-stone-300 focus:border-charcoal focus:ring-charcoal @error('phone') border-red-400 @enderror">
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_email') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full rounded-md border-stone-300 focus:border-charcoal focus:ring-charcoal @error('email') border-red-400 @enderror">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="city" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_city') }}</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" class="w-full rounded-md border-stone-300 focus:border-charcoal focus:ring-charcoal @error('city') border-red-400 @enderror">
                        @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="service_id" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_service') }}</label>
                        <select name="service_id" id="service_id" class="w-full rounded-md border-stone-300 focus:border-charcoal focus:ring-charcoal @error('service_id') border-red-400 @enderror">
                            <option value="">{{ __('site.contact.choose_service') }}</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('service_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="stone_type_id" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_stone_type') }}</label>
                    <select name="stone_type_id" id="stone_type_id" class="w-full rounded-md border-stone-300 focus:border-charcoal focus:ring-charcoal @error('stone_type_id') border-red-400 @enderror">
                        <option value="">{{ __('site.contact.choose_stone_type') }}</option>
                        @foreach ($stoneTypes as $stoneType)
                            <option value="{{ $stoneType->id }}" @selected(old('stone_type_id') == $stoneType->id)>{{ $stoneType->name }}</option>
                        @endforeach
                    </select>
                    @error('stone_type_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-charcoal mb-1">{{ __('site.contact.form_message') }}</label>
                    <textarea name="message" id="message" rows="4" class="w-full rounded-md border-stone-300 focus:border-charcoal focus:ring-charcoal @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center rounded-md bg-charcoal px-8 py-3 text-sm font-semibold text-cream hover:bg-charcoal-700 transition-colors">
                    {{ __('site.common.send_message') }}
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-stone-200 bg-white p-6">
                <h2 class="font-semibold text-charcoal mb-4">{{ __('site.contact.get_in_touch') }}</h2>
                <ul class="space-y-3 text-sm text-stone-600">
                    @if ($settings->phone)
                        <li class="flex items-center gap-2"><x-heroicon-o-phone class="h-4 w-4 text-charcoal shrink-0" /> {{ $settings->phone }}</li>
                    @endif
                    @if ($settings->email)
                        <li class="flex items-center gap-2"><x-heroicon-o-envelope class="h-4 w-4 text-charcoal shrink-0" /> {{ $settings->email }}</li>
                    @endif
                    @if ($settings->address)
                        <li class="flex items-center gap-2"><x-heroicon-o-map-pin class="h-4 w-4 text-charcoal shrink-0" /> {{ $settings->address }}</li>
                    @endif
                    @if ($settings->working_hours)
                        <li class="flex items-center gap-2"><x-heroicon-o-clock class="h-4 w-4 text-charcoal shrink-0" /> {{ $settings->working_hours }}</li>
                    @endif
                </ul>
                @if ($settings->whatsapp)
                    <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank" rel="noopener" class="mt-6 flex items-center justify-center gap-2 rounded-md bg-green-600 hover:bg-green-700 transition-colors text-white text-sm font-semibold px-5 py-3">
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
                        src="https://maps.google.com/maps?q={{ $settings->map_lat }},{{ $settings->map_lng }}&z=14&output=embed">
                    </iframe>
                </div>
            @endif
        </div>
    </section>
</x-layout>
