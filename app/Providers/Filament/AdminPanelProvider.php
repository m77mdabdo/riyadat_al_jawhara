<?php

namespace App\Providers\Filament;

use App\Enums\AdminNavigationGroup;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Help;
use App\Filament\Support\InitialsAvatarProvider;
use App\Http\Middleware\RequirePasswordChange;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetAdminLocale;
use App\Models\Setting;
use Filament\FontProviders\LocalFontProvider;
use Filament\Forms\Components\Field;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Infolists\Components\Entry;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Every label (explicit or generated from the field name) goes through
        // the translator, so lang/ar.json covers the Arabic panel.
        Field::configureUsing(fn (Field $component) => $component->translateLabel());
        Column::configureUsing(fn (Column $column) => $column->translateLabel());
        BaseFilter::configureUsing(fn (BaseFilter $filter) => $filter->translateLabel());
        Entry::configureUsing(fn (Entry $entry) => $entry->translateLabel());

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Forgotten-password flow (emails a signed reset link through the
            // configured mailer) and a self-service profile page for changing
            // name, email and password.
            ->passwordReset()
            ->profile(EditProfile::class, isSimple: false)
            ->brandName('JRA Admin')
            // The company logo from Branding (dark-mode variant: the light logo).
            ->brandLogo(fn (): string => Setting::current()->logoUrl())
            ->darkModeBrandLogo(fn (): string => Setting::current()->logoLightUrl())
            ->brandLogoHeight(fn (): string => auth()->check() ? '2.75rem' : '5rem')
            ->defaultAvatarProvider(InitialsAvatarProvider::class)
            // Sidebar groups in a fixed order; labels follow the admin's language.
            ->navigationGroups(collect(AdminNavigationGroup::cases())->mapWithKeys(fn (AdminNavigationGroup $group) => [
                $group->value => NavigationGroup::make()->label(fn (): string => $group->getLabel())->icon($group->getIcon()),
            ])->all())
            // Warn before leaving a form with unsaved changes; Ctrl/Cmd+K opens search.
            ->unsavedChangesAlerts()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            // Bell icon: new leads, follow-ups due, comments awaiting review.
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            // Self-hosted fonts (resources/css/admin.css): Inter / Cairo in RTL.
            ->font('Inter Variable', provider: LocalFontProvider::class)
            ->renderHook(PanelsRenderHook::SIMPLE_PAGE_START, fn (): string => view('filament.auth.language-switch')->render())
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => Blade::render("@vite('resources/css/admin.css')"))
            ->userMenuItems([
                'help' => MenuItem::make()
                    ->label(fn (): string => __('Help'))
                    ->icon('heroicon-o-question-mark-circle')
                    ->url(fn (): string => Help::getUrl()),
                'locale-ar' => MenuItem::make()
                    ->label('العربية')
                    ->icon('heroicon-o-language')
                    ->postAction(fn (): string => route('admin.locale', 'ar'))
                    ->visible(fn (): bool => app()->getLocale() !== 'ar'),
                'locale-en' => MenuItem::make()
                    ->label('English')
                    ->icon('heroicon-o-language')
                    ->postAction(fn (): string => route('admin.locale', 'en'))
                    ->visible(fn (): bool => app()->getLocale() !== 'en'),
            ])
            ->colors([
                'primary' => Color::hex('#2B2B2B'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SecurityHeaders::class,
            ])
            // Persistent so Livewire updates render in the same language.
            ->middleware([SetAdminLocale::class], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
                RequirePasswordChange::class,
            ]);
    }
}
