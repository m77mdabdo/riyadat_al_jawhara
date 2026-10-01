<?php

namespace App\Filament\Resources;

use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\ManagesPublicRecords;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\FaqResource\Pages;
use App\Filament\Support\Bilingual;
use App\Filament\Support\Localized;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    use ManagesPublicRecords, TranslatesResourceLabels;

    protected static ?string $model = Faq::class;

    protected static ?string $modelLabel = 'FAQ';

    protected static ?string $pluralModelLabel = 'FAQs';

    protected static ?string $navigationGroup = AdminNavigationGroup::WebsiteContent->value;

    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Question and answer'))
                    ->description(__('Shown on the FAQ page; the first ones also appear on the home page.'))
                    ->schema([
                        Bilingual::tabs([
                            'question' => fn (string $l) => Forms\Components\TextInput::make("question_{$l}")->label(Bilingual::label('Question', $l))->required()->maxLength(255),
                            'answer' => fn (string $l) => Forms\Components\Textarea::make("answer_{$l}")->label(Bilingual::label('Answer', $l))->required()->rows(4),
                        ]),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText(__('Inactive items are hidden from the website.')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::publicRecordTable($table->columns([
            Tables\Columns\TextColumn::make(Localized::column('question'))->label(__('Question'))->searchable(['question_en', 'question_ar'])->wrap(),
            Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
        ]), actions: [static::previewAction('faq')]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
