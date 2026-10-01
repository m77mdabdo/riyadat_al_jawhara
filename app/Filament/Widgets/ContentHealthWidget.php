<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use App\Models\Setting;
use App\Support\Dashboard;
use Filament\Widgets\Widget;

/**
 * Content that needs attention (missing images, translations, contact
 * details), each with a link to a filtered list to fix it.
 */
class ContentHealthWidget extends Widget
{
    protected static string $view = 'filament.widgets.content-health';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Service::class) ?? false;
    }

    /**
     * @return list<array{key: string, count: int, url: string}>
     */
    public function problems(): array
    {
        $canSettings = auth()->user()?->can('update', Setting::class) ?? false;

        return array_values(array_filter(
            Dashboard::contentHealth(),
            fn (array $check) => $check['count'] > 0 && ($canSettings || ! in_array($check['key'], ['contact_missing', 'map_missing'], true)),
        ));
    }
}
