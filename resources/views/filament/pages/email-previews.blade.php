<x-filament-panels::page>
    <div class="flex flex-wrap items-end gap-4">
        <label class="block">
            <span class="block text-sm font-medium mb-1">{{ __('Template') }}</span>
            <select wire:model.live="template" class="fi-input rounded-lg border-gray-300 text-sm dark:bg-white/5">
                @foreach ($this->templateOptions() as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="block text-sm font-medium mb-1">{{ __('Language') }}</span>
            <select wire:model.live="locale" class="fi-input rounded-lg border-gray-300 text-sm dark:bg-white/5">
                <option value="ar">العربية</option>
                <option value="en">English</option>
            </select>
        </label>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Subject') }}: <strong dir="auto">{{ $this->previewSubject() }}</strong></p>
    </div>

    {{-- srcdoc keeps the email's own styles away from the admin page. --}}
    <iframe title="{{ __('Email previews') }}" srcdoc="{{ $this->previewHtml() }}" class="w-full rounded-xl border border-gray-200 bg-white dark:border-white/10" style="height: 80vh;"></iframe>
</x-filament-panels::page>
