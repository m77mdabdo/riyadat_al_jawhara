<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Enums\PageKey;
use App\Filament\Resources\PageResource;
use App\Filament\Support\SiteTextFields;
use App\Models\Page;
use App\Models\Setting;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

/**
 * @property Page $record
 */
class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    public function getTitle(): string
    {
        return __('Page').': '.$this->record->key->getLabel();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewOnSite')
                ->label(__('View on site'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => $this->record->publicUrl(), shouldOpenInNewTab: true)
                ->visible(fn () => $this->record->publicUrl() !== null),
        ];
    }

    /**
     * @return list<string>
     */
    private function textKeys(): array
    {
        $key = $this->record->key;
        $keys = [$key->titleKey(), $key->subtitleKey()];

        return $key === PageKey::Contact ? [...$keys, ...array_keys(PageResource::CONTACT_TEXTS)] : $keys;
    }

    /**
     * @return list<string>
     */
    private function settingFields(): array
    {
        return PageResource::canEditSettingFields() ? (PageResource::SETTING_FIELDS[$this->record->key->value] ?? []) : [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $setting = Setting::current();
        $data['setting'] = Arr::only($setting->getAttributes(), $this->settingFields());

        return $data + SiteTextFields::state($this->textKeys());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        SiteTextFields::save($data);

        $fields = $this->settingFields();
        if ($fields !== []) {
            Setting::editable()->fill(Arr::only($data['setting'] ?? [], $fields))->save();
        }
        unset($data['setting']);

        return $data;
    }
}
