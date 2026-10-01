<?php

namespace App\Filament\Pages\Concerns;

use App\Filament\Support\SiteTextFields;
use App\Models\Setting;
use App\Support\FragmentCache;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;

/**
 * A page that edits part of the settings row plus some public texts
 * (Branding, Footer, Floating elements, Homepage, the Settings sub-pages).
 * The using page defines schema() and, optionally, siteTextKeys().
 *
 * @property Forms\Form $form
 */
trait EditsSiteSettings
{
    use Forms\Concerns\InteractsWithForms;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /**
     * @return array<int, Forms\Components\Component>
     */
    abstract protected function schema(): array;

    /**
     * Public texts edited on this page (full keys, "site.…").
     *
     * @return list<string>
     */
    protected function siteTextKeys(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->form->fill($this->mutateFillData($this->getSetting()->attributesToArray() + SiteTextFields::state($this->siteTextKeys())));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFillData(array $data): array
    {
        return $data;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function mutateSaveData(array $state): array
    {
        return $state;
    }

    public function getSetting(): Setting
    {
        return Setting::editable();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->model($this->getSetting())->schema($this->schema())->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $state = $this->form->getState();
        SiteTextFields::save($state);
        $state = $this->mutateSaveData($state);

        $setting = $this->getSetting();
        $setting->fill($state)->save();
        $this->form->model($setting)->saveRelationships();
        // Media uploads and texts don't always touch the settings row.
        Setting::flushCache();
        FragmentCache::flush();

        Notification::make()->title(__('Saved'))->success()->send();
    }

    /**
     * @return array<int, Action>
     */
    protected function getFormActions(): array
    {
        return [Action::make('save')->label(__('Save'))->submit('save')->keyBindings(['mod+s'])];
    }
}
