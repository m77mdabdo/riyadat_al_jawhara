<?php

namespace App\Filament\Concerns;

use App\Models\Contracts\HasPublicUrl;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Shared table behaviour for records shown on the public site:
 * "View on site", duplicate, trash / restore (force delete for admins via
 * policies), bulk activate / deactivate, active + trash filters and
 * drag-and-drop ordering when the model has an `order` column.
 */
trait ManagesPublicRecords
{
    /**
     * Trashed records stay reachable in the admin (Trash filter, restore).
     *
     * @return Builder<Model>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    protected static function modelHas(string $column): bool
    {
        $model = static::getModel();

        return in_array($column, (new $model)->getFillable(), true);
    }

    /**
     * Applies the shared actions, bulk actions, filters and reordering.
     *
     * @param  list<Tables\Filters\BaseFilter>  $filters  resource-specific filters
     */
    public static function publicRecordTable(Table $table, array $filters = []): Table
    {
        if (static::modelHas('order')) {
            $table->reorderable('order')->defaultSort('order');
        }

        $shared = [Tables\Filters\TrashedFilter::make()];
        if (static::modelHas('is_active')) {
            array_unshift($shared, Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')));
        }

        return $table
            ->filters([...$filters, ...$shared])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    static::viewOnSiteTableAction(),
                    Tables\Actions\EditAction::make(),
                    static::duplicateAction(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make(array_values(array_filter([
                    static::modelHas('is_active') ? static::setActiveBulkAction(true) : null,
                    static::modelHas('is_active') ? static::setActiveBulkAction(false) : null,
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]))),
            ]);
    }

    public static function viewOnSiteTableAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('viewOnSite')
            ->label(__('View on site'))
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->url(fn (Model $record): ?string => $record instanceof HasPublicUrl ? $record->publicUrl() : null, shouldOpenInNewTab: true)
            ->visible(fn (Model $record): bool => ! ($record->deleted_at ?? null) && $record instanceof HasPublicUrl && $record->publicUrl() !== null);
    }

    /**
     * Copies the record as an inactive draft. Images and relations are not
     * copied; sluggable models get a fresh unique slug from the new name.
     */
    public static function duplicateAction(): Tables\Actions\ReplicateAction
    {
        return Tables\Actions\ReplicateAction::make('duplicate')
            ->label(__('Duplicate'))
            ->excludeAttributes(['deleted_at', 'views_count', 'slug'])
            ->beforeReplicaSaved(function (Model $replica): void {
                foreach (['name_en', 'title_en', 'question_en', 'client_name'] as $field) {
                    if (filled($replica->getAttribute($field))) {
                        $replica->setAttribute($field, $replica->getAttribute($field).' ('.__('copy').')');
                        break;
                    }
                }
                foreach (['is_active', 'is_published', 'is_featured'] as $flag) {
                    if (in_array($flag, $replica->getFillable(), true)) {
                        $replica->setAttribute($flag, false);
                    }
                }
            })
            ->successNotificationTitle(__('Copy created as an inactive draft'));
    }

    protected static function setActiveBulkAction(bool $active): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make($active ? 'activate' : 'deactivate')
            ->label(__($active ? 'Activate' : 'Deactivate'))
            ->icon($active ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
            ->authorize(fn (): bool => static::canEditAny())
            ->action(function (Collection $records) use ($active): void {
                $records->each(fn (Model $record) => $record->update(['is_active' => $active]));
                Notification::make()->title(__($active ? 'Activated' : 'Deactivated'))->success()->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    public static function canEditAny(): bool
    {
        return auth()->user()?->can('update', static::getModel()) ?? false;
    }
}
