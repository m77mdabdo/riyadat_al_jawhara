<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Leads whose follow-up date is today or past.
 */
class FollowUpsDue extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Lead::class) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Follow-ups due'))
            ->query(Lead::followUpDue()->with('assignee')->orderBy('follow_up_at'))
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading(__('Nothing overdue'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->weight('medium'),
                Tables\Columns\TextColumn::make('phone')->label(__('Phone'))->extraAttributes(['dir' => 'ltr']),
                Tables\Columns\TextColumn::make('follow_up_at')->label(__('Follow-up'))->date()->color('danger'),
                Tables\Columns\TextColumn::make('assignee.name')->label(__('Assigned to'))->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()
                    ->formatStateUsing(fn (?string $state) => LeadStatus::labelFor($state))
                    ->color(fn (?string $state) => LeadStatus::tryFrom((string) $state)?->getColor() ?? 'gray'),
            ])
            ->recordUrl(fn (Lead $record) => LeadResource::getUrl('view', ['record' => $record]));
    }
}
