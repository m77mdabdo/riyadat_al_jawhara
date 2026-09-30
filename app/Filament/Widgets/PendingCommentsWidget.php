<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingCommentsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $pending = Comment::where('status', 'pending')->count();

        return [
            Stat::make(__('Pending Comments'), $pending)
                ->description($pending > 0 ? __('Awaiting moderation') : __('All caught up'))
                ->descriptionIcon($pending > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color($pending > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.comments.index', ['tableFilters[status][value]' => 'pending'])),
        ];
    }
}
