<?php

namespace App\Filament\Widgets;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * The ten newest requests, with an in-place status change.
 */
class LatestLeads extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Lead::class) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Latest leads'))
            ->query(Lead::query()->with('service')->latest()->limit(10))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->weight('medium'),
                Tables\Columns\TextColumn::make('phone')->label(__('Phone'))->extraAttributes(['dir' => 'ltr']),
                Tables\Columns\TextColumn::make('source')->label(__('Source'))->badge()
                    ->formatStateUsing(fn (?string $state) => LeadSource::labelFor($state)),
                Tables\Columns\TextColumn::make('service.name')->label(__('Service'))->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label(__('Received'))->since(),
                Tables\Columns\SelectColumn::make('status')->label(__('Status'))
                    ->options(LeadStatus::options())
                    ->selectablePlaceholder(false)
                    ->disabled(fn (Lead $record) => ! (auth()->user()?->can('update', $record) ?? false)),
            ])
            ->recordUrl(fn (Lead $record) => LeadResource::getUrl('view', ['record' => $record]));
    }
}
