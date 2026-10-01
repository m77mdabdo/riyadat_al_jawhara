<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Homepage;
use App\Filament\Pages\SiteTexts;
use App\Filament\Resources\LeadResource;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\StoneTypeResource;
use Filament\Widgets\Widget;

/**
 * Shortcuts to the most common tasks the signed-in user is allowed to do.
 */
class QuickActions extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';

    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return list<array{label: string, url: string, icon: string}>
     */
    public function actions(): array
    {
        $actions = [
            [LeadResource::class, 'index', 'Open leads', 'heroicon-o-inbox-arrow-down'],
            [ProjectResource::class, 'create', 'New project', 'heroicon-o-photo'],
            [StoneTypeResource::class, 'create', 'New stone', 'heroicon-o-cube'],
            [PostResource::class, 'create', 'New article', 'heroicon-o-newspaper'],
        ];

        $links = [];
        foreach ($actions as [$resource, $page, $label, $icon]) {
            $allowed = $page === 'create' ? $resource::canCreate() : $resource::canViewAny();
            if ($allowed) {
                $links[] = ['label' => __($label), 'url' => $resource::getUrl($page), 'icon' => $icon];
            }
        }
        foreach ([[Homepage::class, 'Edit the home page', 'heroicon-o-home'], [SiteTexts::class, 'Edit site texts', 'heroicon-o-language']] as [$page, $label, $icon]) {
            if ($page::canAccess()) {
                $links[] = ['label' => __($label), 'url' => $page::getUrl(), 'icon' => $icon];
            }
        }
        $links[] = ['label' => __('View the website'), 'url' => route('home'), 'icon' => 'heroicon-o-arrow-top-right-on-square'];

        return $links;
    }
}
