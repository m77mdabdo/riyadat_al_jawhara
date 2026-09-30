<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetAdminLocale;
use Filament\Forms\Components\Field;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Infolists\Components\Entry;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->userMenuItems([
                MenuItem::make('locale-ar')
                    ->label('العربية')
                    ->icon('heroicon-o-language')
                    ->postAction(fn (): string => route('admin.locale', 'ar'))
                    ->visible(fn (): bool => app()->getLocale() !== 'ar'),
                MenuItem::make('locale-en')
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
                Widgets\FilamentInfoWidget::class,
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
            ]);
    }
}
