<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Models\LeadNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Internal notes on a lead. Any staff member can add a note; a note can be
 * edited or deleted by its author or by an admin.
 */
class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Internal notes';

    public function isReadOnly(): bool
    {
        // Notes are the follow-up log, so they stay writable on the View page too.
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('body')
                    ->label('Note')
                    ->required()
                    ->maxLength(5000)
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->columns([
                Tables\Columns\TextColumn::make('body')->label('Note')->wrap(),
                Tables\Columns\TextColumn::make('user.name')->label('By')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('When')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add note')
                    ->mutateFormDataUsing(fn (array $data): array => $data + ['user_id' => auth()->id()]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn (LeadNote $record): bool => $this->canManage($record)),
                Tables\Actions\DeleteAction::make()->visible(fn (LeadNote $record): bool => $this->canManage($record)),
            ]);
    }

    private function canManage(LeadNote $note): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->hasRole('admin') || $note->user_id === $user->id);
    }
}
