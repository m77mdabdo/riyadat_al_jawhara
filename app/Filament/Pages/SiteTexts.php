<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Models\SiteText;
use App\Support\SiteTexts as Texts;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

/**
 * Every public interface text (buttons, labels, messages, emails) in Arabic
 * and English side by side. Empty = the text shipped with the site.
 *
 * @property Forms\Form $form
 * @property Forms\Form $filtersForm
 */
class SiteTexts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.site-texts';

    protected static ?int $navigationSort = 90;

    public const PER_PAGE = 15;

    /** @var array<string, mixed> */
    public array $filters = ['search' => '', 'group' => null];

    /** @var array<string, array{ar: ?string, en: ?string}> */
    public array $values = [];

    public int $page = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('update', SiteText::class) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavigationGroup::WebsiteContent->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('Site texts');
    }

    public function getTitle(): string
    {
        return __('Site texts');
    }

    public function getSubheading(): string
    {
        return __('Buttons, labels and messages of the public site and its emails. Leave a field empty to use the original text, shown in grey.');
    }

    public function mount(): void
    {
        $this->filtersForm->fill($this->filters);
        $this->loadValues();
    }

    /**
     * @return array<int|string, string|Forms\Form>
     */
    protected function getForms(): array
    {
        return ['filtersForm', 'form'];
    }

    public function filtersForm(Forms\Form $form): Forms\Form
    {
        $groups = collect(array_keys(Texts::editableKeys()))
            ->mapWithKeys(fn (string $group) => [$group => self::groupLabel($group)])->all();

        return $form->schema([
            Forms\Components\TextInput::make('search')
                ->label(__('Search'))
                ->placeholder(__('Word in Arabic or English'))
                ->live(debounce: 400)
                ->afterStateUpdated(fn () => $this->goTo(1)),
            Forms\Components\Select::make('group')
                ->label(__('Section'))
                ->options($groups)
                ->placeholder(__('All sections'))
                ->live()
                ->afterStateUpdated(fn () => $this->goTo(1)),
        ])->columns(2)->statePath('filters');
    }

    public function form(Forms\Form $form): Forms\Form
    {
        $fields = [];
        foreach ($this->visibleKeys() as $key) {
            $fields[] = Forms\Components\Fieldset::make(self::keyLabel($key))
                ->schema([
                    $this->field($key, 'ar'),
                    $this->field($key, 'en'),
                ])
                ->columns(2);
        }

        return $form->schema($fields)->statePath('values');
    }

    private function field(string $key, string $locale): Forms\Components\Textarea
    {
        $default = (string) Texts::default($key, $locale);

        return Forms\Components\Textarea::make(self::encode($key).'.'.$locale)
            ->label($locale === 'ar' ? __('Arabic') : __('English'))
            ->placeholder($default)
            ->rows(max(1, min(4, (int) ceil(mb_strlen($default) / 70))))
            ->autosize()
            ->maxLength(2000)
            ->extraInputAttributes(['dir' => $locale === 'ar' ? 'rtl' : 'ltr', 'lang' => $locale])
            ->hint(fn (?string $state) => filled($state) ? __('Edited') : null)
            ->hintColor('warning')
            ->hintActions($locale === 'en' ? [] : [Forms\Components\Actions\Action::make('restore')
                ->label(__('Restore original'))
                ->icon('heroicon-m-arrow-uturn-left')
                ->color('gray')
                ->visible(fn () => Texts::value($key, 'ar') !== null || Texts::value($key, 'en') !== null)
                ->requiresConfirmation()
                ->modalHeading(__('Restore the original Arabic and English text?'))
                ->action(fn () => $this->resetKey(self::encode($key)))])
            ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) use ($key, $locale) {
                if ($problem = Texts::problem($key, is_string($value) ? $value : null, $locale)) {
                    $fail($problem);
                }
            });
    }

    /**
     * Keys matching the filters.
     *
     * @return list<string>
     */
    public function matchingKeys(): array
    {
        $group = $this->filters['group'] ?? null;
        $search = Str::lower(trim((string) ($this->filters['search'] ?? '')));
        $keys = [];

        foreach (Texts::editableKeys() as $name => $groupKeys) {
            if ($group && $group !== $name) {
                continue;
            }
            foreach ($groupKeys as $key) {
                if ($search === '' || Str::contains(Str::lower(implode(' ', [
                    $key,
                    Texts::default($key, 'ar'), Texts::default($key, 'en'),
                    Texts::value($key, 'ar'), Texts::value($key, 'en'),
                ])), $search)) {
                    $keys[] = $key;
                }
            }
        }

        return $keys;
    }

    /**
     * @return list<string>
     */
    public function visibleKeys(): array
    {
        return array_slice($this->matchingKeys(), ($this->page - 1) * self::PER_PAGE, self::PER_PAGE);
    }

    public function lastPage(): int
    {
        return max(1, (int) ceil(count($this->matchingKeys()) / self::PER_PAGE));
    }

    public function goTo(int $page): void
    {
        $this->page = max(1, min($page, $this->lastPage()));
        $this->loadValues();
    }

    private function loadValues(): void
    {
        $values = [];
        foreach ($this->visibleKeys() as $key) {
            $values[self::encode($key)] = ['ar' => Texts::value($key, 'ar'), 'en' => Texts::value($key, 'en')];
        }
        $this->form->fill($values);
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $state = $this->form->getState();
        foreach ($this->visibleKeys() as $key) {
            $row = $state[self::encode($key)] ?? [];
            Texts::put($key, $row['ar'] ?? null, $row['en'] ?? null);
        }
        $this->loadValues();

        Notification::make()->title(__('Texts saved'))->success()->send();
    }

    public function resetKey(string $encoded): void
    {
        abort_unless(static::canAccess(), 403);

        $key = self::decode($encoded);
        if (Texts::isEditable($key)) {
            Texts::put($key, null, null);
        }
        $this->loadValues();

        Notification::make()->title(__('Original text restored'))->success()->send();
    }

    public static function encode(string $key): string
    {
        return str_replace('.', '~', $key);
    }

    public static function decode(string $encoded): string
    {
        return str_replace('~', '.', $encoded);
    }

    public static function groupLabel(string $group): string
    {
        $key = 'admin.site_text_groups.'.$group;

        return trans()->has($key) ? __($key) : Str::headline($group);
    }

    private static function keyLabel(string $key): string
    {
        $path = Str::after($key, Texts::GROUP.'.');

        return self::groupLabel(Str::before($path, '.')).' › '.str_replace('.', ' › ', Str::after($path, '.'));
    }
}
