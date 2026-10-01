<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\ActivityResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/**
 * Read-only audit trail of admin changes (see Models\Concerns\LogsAdminActivity).
 * Admins only; nobody can create, edit or delete entries from the panel.
 */
class ActivityResource extends Resource
{
    use TranslatesResourceLabels;

    protected static ?string $model = Activity::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Administration->value;

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return __('admin.activity.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.activity.plural');
    }

    /** @return Builder<Activity> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['causer', 'subject']);
    }

    /**
     * Admin resource that manages a model class, if any.
     *
     * @return class-string<resource>|null
     */
    public static function resourceFor(?string $type): ?string
    {
        if (! $type) {
            return null;
        }

        return collect(filament()->getPanel('admin')->getResources())->first(fn (string $resource) => $resource::getModel() === $type);
    }

    /** Where the changed record can be opened, while it still exists. */
    public static function subjectUrl(Activity $activity): ?string
    {
        $resource = self::resourceFor($activity->subject_type);
        $subject = $activity->subject;
        if (! $resource || ! $subject instanceof Model) {
            return null;
        }

        foreach (['edit', 'view'] as $page) {
            if ($resource::hasPage($page) && ($page === 'view' ? $resource::canView($subject) : $resource::canEdit($subject))) {
                return $resource::getUrl($page, ['record' => $subject]);
            }
        }

        return null;
    }

    /** "Project #12" style label for the subject column. */
    public static function subjectLabel(Activity $activity): string
    {
        return class_basename((string) $activity->subject_type).' #'.$activity->subject_id;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.activity.when'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('admin.activity.user'))
                    ->placeholder(__('admin.activity.system')),
                Tables\Columns\TextColumn::make('event')
                    ->label(__('admin.activity.event'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? __('admin.activity.events.'.$state) : '—')
                    ->color(fn (?string $state) => match ($state) {
                        'created' => 'success',
                        'deleted' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('admin.activity.subject'))
                    ->formatStateUsing(fn ($state, Activity $record) => static::subjectLabel($record)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('admin.activity.event'))
                    ->options(collect(['created', 'updated', 'deleted'])
                        ->mapWithKeys(fn ($e) => [$e => __('admin.activity.events.'.$e)])
                        ->all()),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label(__('admin.activity.subject'))
                    ->options(fn () => Activity::query()->distinct()->orderBy('subject_type')->pluck('subject_type')
                        ->filter()
                        ->mapWithKeys(fn ($type) => [$type => ($resource = static::resourceFor($type)) ? $resource::getPluralModelLabel() : class_basename($type)])
                        ->all()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('From')),
                        Forms\Components\DatePicker::make('until')->label(__('Until')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        ($data['from'] ?? null) ? __('From').' '.$data['from'] : null,
                        ($data['until'] ?? null) ? __('Until').' '.$data['until'] : null,
                    ])),
                Tables\Filters\SelectFilter::make('causer_id')
                    ->label(__('admin.activity.user'))
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('openRecord')
                    ->label(__('Open record'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Activity $record) => static::subjectUrl($record))
                    ->visible(fn (Activity $record) => static::subjectUrl($record) !== null),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')->label(__('admin.activity.when'))->dateTime(),
                    Infolists\Components\TextEntry::make('causer.name')->label(__('admin.activity.user'))
                        ->placeholder(__('admin.activity.system')),
                    Infolists\Components\TextEntry::make('event')->label(__('admin.activity.event'))
                        ->formatStateUsing(fn (?string $state) => $state ? __('admin.activity.events.'.$state) : '—'),
                    Infolists\Components\TextEntry::make('subject_type')->label(__('admin.activity.subject'))
                        ->formatStateUsing(fn ($state, Activity $record) => static::subjectLabel($record))
                        ->url(fn (Activity $record) => static::subjectUrl($record))
                        ->color(fn (Activity $record) => static::subjectUrl($record) ? 'primary' : null),
                ]),
            Infolists\Components\Section::make(__('admin.activity.changes'))
                ->schema([
                    Infolists\Components\ViewEntry::make('properties')
                        ->label(__('admin.activity.changes'))
                        ->hiddenLabel()
                        ->view('filament.activity.changes'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'view' => Pages\ViewActivity::route('/{record}'),
        ];
    }

    // Viewing is governed by ActivityPolicy (admins only). Nobody, admins
    // included, may create, edit or delete audit entries from the panel.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
