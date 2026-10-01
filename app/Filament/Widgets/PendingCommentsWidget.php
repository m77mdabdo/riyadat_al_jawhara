<?php

namespace App\Filament\Widgets;

use App\Enums\CommentStatus;
use App\Models\Comment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingCommentsWidget extends BaseWidget
{
    /** Figures are cached for minutes; reloading the page is enough. */
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $pending = Comment::where('status', CommentStatus::Pending->value)->count();

        return [
            Stat::make(__('Pending Comments'), $pending)
                ->description($pending > 0 ? __('Awaiting moderation') : __('All caught up'))
                ->descriptionIcon($pending > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color($pending > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.comments.index', ['tableFilters[status][value]' => CommentStatus::Pending->value])),
        ];
    }
}
