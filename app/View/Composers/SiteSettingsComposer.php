<?php

namespace App\View\Composers;

use App\Models\Setting;
use Illuminate\View\View;

/**
 * Shares the site settings (contact details, logo, socials) with the layout
 * pieces, so templates never query them themselves. A controller that
 * already passed `$settings` keeps its own instance.
 */
class SiteSettingsComposer
{
    public function compose(View $view): void
    {
        if (! array_key_exists('settings', $view->getData())) {
            $view->with('settings', $this->settings($view));
        }
    }

    private function settings(View $view): ?Setting
    {
        // Error pages must render even when the database is unavailable.
        return $view->name() === 'components.error-page'
            ? rescue(fn () => Setting::current(), null, false)
            : Setting::current();
    }
}
