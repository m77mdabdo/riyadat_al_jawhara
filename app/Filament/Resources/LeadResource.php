<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers\NotesRelationManager;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends Resource
{
    use RestrictsDeletionToAdmins;

    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Leads';

    protected static ?string $navigationBadgeTooltip = 'New leads';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'new')->count();
    }

    /**
     * Staff who can own a lead: anyone with panel access.
     *
     * @return array<int, string>
     */
    public static function assigneeOptions(): array
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['admin', 'editor']))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contact')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Request')
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->label('Service')
                            ->options(fn () => Service::ordered()->pluck('name_en', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('stone_type_id')
                            ->label('Stone Type')
                            ->options(fn () => StoneType::ordered()->pluck('name_en', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('project_id')
                            ->label('Related Project')
                            ->options(fn () => Project::ordered()->pluck('title_en', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('source')
                            ->options(Lead::SOURCES)
                            ->required(),
                        Forms\Components\Textarea::make('message')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Follow-up')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(Lead::STATUSES)
                            ->default('new')
                            ->required(),
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned to')
                            ->options(fn () => static::assigneeOptions())
                            ->searchable(),
                        Forms\Components\DatePicker::make('follow_up_at')
                            ->label('Follow-up date'),
                    ])->columns(3),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Contact')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('phone')
                            ->url(fn (Lead $record): string => 'tel:'.preg_replace('/[^\d+]/', '', $record->phone))
                            ->copyable(),
                        Infolists\Components\TextEntry::make('email')
                            ->placeholder('—')
                            ->url(fn (Lead $record): ?string => $record->email ? 'mailto:'.$record->email : null)
                            ->copyable(),
                        Infolists\Components\TextEntry::make('city')->placeholder('—'),
                    ])->columns(4),

                Infolists\Components\Section::make('Request')
                    ->schema([
                        Infolists\Components\TextEntry::make('service.name_en')->label('Service')->placeholder('—'),
                        Infolists\Components\TextEntry::make('stoneType.name_en')->label('Stone type')->placeholder('—'),
                        Infolists\Components\TextEntry::make('project.title_en')->label('Related project')->placeholder('—'),
                        Infolists\Components\TextEntry::make('source')->badge()->formatStateUsing(fn (string $state): string => Lead::SOURCES[$state] ?? $state),
                        Infolists\Components\TextEntry::make('message')->columnSpanFull()->prose(),
                    ])->columns(4),

                Infolists\Components\Section::make('Follow-up')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => static::statusColor($state))
                            ->formatStateUsing(fn (string $state): string => Lead::STATUSES[$state] ?? $state),
                        Infolists\Components\TextEntry::make('assignee.name')->label('Assigned to')->placeholder('Unassigned'),
                        Infolists\Components\TextEntry::make('follow_up_at')->label('Follow-up date')->date()->placeholder('—')
                            ->color(fn (Lead $record): ?string => $record->follow_up_at?->isPast() && $record->status !== 'closed' ? 'danger' : null),
                        Infolists\Components\TextEntry::make('created_at')->label('Received')->dateTime(),
                    ])->columns(4),

                Infolists\Components\Section::make('Submission details')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('locale')->label('Language')->formatStateUsing(fn (?string $state): string => strtoupper((string) $state))->placeholder('—'),
                        Infolists\Components\TextEntry::make('consented_at')->label('Privacy consent')->dateTime()->placeholder('—'),
                        Infolists\Components\TextEntry::make('ip_address')->label('IP address')->placeholder('—'),
                        Infolists\Components\TextEntry::make('source_url')->label('Submitted from')->placeholder('—')->columnSpanFull(),
                        Infolists\Components\TextEntry::make('user_agent')->label('Browser')->placeholder('—')->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'new' => 'warning',
            'contacted' => 'info',
            'in_progress' => 'primary',
            'closed' => 'success',
            default => 'gray',
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['service', 'stoneType', 'project', 'assignee']))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('city')->toggleable(),
                Tables\Columns\TextColumn::make('service.name_en')->label('Service')->toggleable(),
                Tables\Columns\TextColumn::make('stoneType.name_en')->label('Stone')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('project.title_en')->label('Project')->limit(30)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('message')->limit(40)->tooltip(fn (Lead $record): ?string => $record->message)->toggleable(),
                Tables\Columns\TextColumn::make('source')->badge()->toggleable(),
                Tables\Columns\SelectColumn::make('status')->options(Lead::STATUSES)->selectablePlaceholder(false),
                Tables\Columns\TextColumn::make('assignee.name')->label('Assigned to')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('follow_up_at')
                    ->label('Follow-up')
                    ->date()
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn (Lead $record): ?string => $record->follow_up_at?->isPast() && $record->status !== 'closed' ? 'danger' : null),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Lead::STATUSES),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Assigned to')
                    ->options(fn () => static::assigneeOptions()),
                Tables\Filters\Filter::make('mine')
                    ->label('Assigned to me')
                    ->query(fn (Builder $query): Builder => $query->where('assigned_to', auth()->id())),
                Tables\Filters\Filter::make('follow_up_due')
                    ->label('Follow-up due')
                    ->query(fn (Builder $query): Builder => $query->followUpDue()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
