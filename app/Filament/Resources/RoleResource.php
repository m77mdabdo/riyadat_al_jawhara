<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Enums\UserRole;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\RoleResource\Pages;
use App\Support\Permissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

/**
 * Roles and what they may do, per content area. Administrators can always
 * do everything; the editor role starts with what editors could always do.
 */
class RoleResource extends Resource
{
    use TranslatesResourceLabels;

    protected static ?string $model = Role::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Administration->value;

    protected static ?int $navigationSort = 20;

    public static function roleLabel(string $name): string
    {
        return in_array($name, UserRole::values(), true) ? UserRole::from($name)->getLabel() : $name;
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof Role && $record->name !== UserRole::Admin->value && parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        // Built-in roles and roles in use stay, also for administrators.
        return $record instanceof Role
            && ! in_array($record->name, UserRole::values(), true)
            && ! $record->users()->exists()
            && parent::canDelete($record);
    }

    public static function form(Form $form): Form
    {
        $abilities = collect(Permissions::ABILITIES)->mapWithKeys(fn (string $ability) => [$ability => __('admin.permissions.abilities.'.$ability)])->all();

        return $form->schema([
            Forms\Components\Section::make(__('Role'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(60)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?Role $record) => $record && in_array($record->name, UserRole::values(), true))
                        ->helperText(__('For example: Blog writer, Sales.')),
                ]),
            Forms\Components\Section::make(__('Content'))
                ->description(__('“Delete” also allows restoring from the trash. Permanent deletion is for administrators only.'))
                ->schema(array_map(
                    fn (string $area) => Forms\Components\CheckboxList::make("abilities.{$area}")
                        ->label(__('admin.permissions.areas.'.$area))
                        ->options($abilities)
                        ->columns(4)
                        ->bulkToggleable(),
                    Permissions::AREAS,
                ))
                ->columns(2),
            Forms\Components\Section::make(__('Special permissions'))
                ->schema([
                    Forms\Components\CheckboxList::make('special')
                        ->label(__('Special permissions'))
                        ->hiddenLabel()
                        ->options(collect(Permissions::SPECIAL)->mapWithKeys(fn (string $name) => [$name => __('admin.permissions.special.'.str_replace('.', '_', $name))])->all())
                        ->columns(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))
                    ->formatStateUsing(fn (string $state) => self::roleLabel($state))
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('users_count')->label(__('Users'))->counts('users'),
                Tables\Columns\TextColumn::make('permissions_count')->label(__('Permissions'))->counts('permissions')
                    ->formatStateUsing(fn (int $state, Role $record) => $record->name === UserRole::Admin->value ? __('All') : (string) $state),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
