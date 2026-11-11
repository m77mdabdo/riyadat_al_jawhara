<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\StaffAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    use TranslatesResourceLabels;

    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = AdminNavigationGroup::Administration->value;

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Toggle::make('send_invitation')
                    ->label(__('Send an invitation email'))
                    ->helperText(__('The person receives a link to choose their own password. Turn off to set a password here instead.'))
                    ->default(true)
                    ->live()
                    ->dehydrated(false)
                    ->visibleOn('create'),
                Forms\Components\TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->maxLength(255)
                    ->rule(Password::defaults())
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation, Forms\Get $get): bool => $operation === 'create' && ! $get('send_invitation'))
                    ->visible(fn (string $operation, Forms\Get $get): bool => $operation !== 'create' || ! $get('send_invitation'))
                    ->helperText(fn (string $operation) => $operation === 'create' ? null : __('Leave blank to keep the current password.')),
                // Options must come from the relationship itself so the stored
                // state is role IDs; overriding them with name keys made
                // sync() insert role names into model_has_roles.role_id.
                Forms\Components\Select::make('roles')
                    ->label(__('Roles'))
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $record): string => RoleResource::roleLabel($record->name))
                    ->multiple()
                    ->preload()
                    ->required()
                    ->helperText(__('What each role may do is set in Administration → Roles.')),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true)
                    ->helperText(__('Inactive accounts cannot sign in; their history is kept.'))
                    ->disabled(fn (?User $record) => $record?->is(auth()->user()) ?? false),
                Forms\Components\Toggle::make('password_change_required')
                    ->label(__('Ask for a new password at next sign-in')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('Email'))->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->label(__('Roles'))->badge()
                    ->formatStateUsing(fn (string $state) => RoleResource::roleLabel($state)),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')->label(__('Last sign-in'))->since()->sortable()->placeholder(__('Never')),
                Tables\Columns\TextColumn::make('created_at')->label(__('Created'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
                Tables\Filters\SelectFilter::make('roles')->label(__('Roles'))->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $record): string => RoleResource::roleLabel($record->name)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('sendAccessLink')
                        ->label(fn (User $record) => $record->last_login_at ? __('Email a password link') : __('Resend invitation'))
                        ->icon('heroicon-o-envelope')
                        ->requiresConfirmation()
                        ->modalDescription(__('The person receives an email with a time-limited link to choose a new password.'))
                        ->visible(fn (User $record) => $record->is_active && $record->isNot(auth()->user()))
                        ->action(fn (User $record) => self::notifyAccessResult(
                            StaffAccess::send($record, invitation: ! $record->last_login_at),
                            $record,
                            invitation: ! $record->last_login_at,
                        )),
                    Tables\Actions\Action::make('requirePassword')
                        ->label(__('Ask for a new password'))
                        ->icon('heroicon-o-key')
                        ->requiresConfirmation()
                        ->modalDescription(__('At the next sign-in this person must choose a new password before doing anything else.'))
                        ->visible(fn (User $record) => ! $record->password_change_required && $record->isNot(auth()->user()))
                        ->action(function (User $record) {
                            $record->update(['password_change_required' => true]);
                            Notification::make()->title(__('A new password will be required'))->success()->send();
                        }),
                    Tables\Actions\Action::make('toggleActive')
                        ->label(fn (User $record) => $record->is_active ? __('Deactivate') : __('Activate'))
                        ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                        ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->visible(fn (User $record) => $record->isNot(auth()->user()))
                        ->action(fn (User $record) => $record->update(['is_active' => ! $record->is_active])),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function notifyAccessResult(bool $sent, User $user, bool $invitation): void
    {
        $sent
            ? Notification::make()->title($invitation ? __('Invitation sent to :email', ['email' => $user->email]) : __('Password link sent to :email', ['email' => $user->email]))->success()->send()
            : Notification::make()->title(__('The email could not be sent'))->body(__('The account is saved. Check the mail settings, then use “Resend invitation”.'))->danger()->persistent()->send();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
