<?php

namespace App\Filament\Resources;

use App\Actions\SiteContent\ImportSiteContent;
use App\Enums\AdminNavigationGroup;
use App\Filament\Concerns\TranslatesResourceLabels;
use App\Filament\Resources\SiteImageResource\Pages;
use App\Filament\Support\ImageUpload;
use App\Models\SiteImage;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

/**
 * Replaceable site images (hero poster, page textures, heritage photos,
 * mobile logo). A slot without an upload keeps its built-in image.
 */
class SiteImageResource extends Resource
{
    use TranslatesResourceLabels;

    protected static ?string $model = SiteImage::class;

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationGroup = AdminNavigationGroup::WebsiteContent->value;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(fn (?SiteImage $record) => $record?->label())
                ->description(fn (?SiteImage $record) => $record ? __('Used on: :place', ['place' => $record->usedOn()]) : null)
                ->schema([
                    Forms\Components\Placeholder::make('current')
                        ->label(__('Shown now'))
                        ->content(fn (?SiteImage $record) => $record?->currentUrl()
                            ? new HtmlString('<img src="'.e($record->currentUrl()).'" alt="" style="max-height:220px;border-radius:0.5rem">')
                            : __('No image (a pattern is shown instead).')),
                    ImageUpload::make('image', 'image')
                        ->label(__('Replace with'))
                        ->helperText(fn (?SiteImage $record) => $record
                            ? __('Recommended :size (:ratio).', ['size' => SiteImage::SLOTS[$record->slot]['size'] ?? '—', 'ratio' => SiteImage::SLOTS[$record->slot]['ratio'] ?? '—']).' '.__('JPG, PNG or WebP, up to :size MB.', ['size' => round(ImageUpload::maxKb() / 1024)]).' '.__('Remove the upload to go back to the built-in image.')
                            : null)
                        ->imageEditor(),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('alt_ar')->label(__('Image description (AR)'))->maxLength(200)->extraInputAttributes(['dir' => 'rtl']),
                        Forms\Components\TextInput::make('alt_en')->label(__('Image description (EN)'))->maxLength(200),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview')->label(__('Image'))
                    ->state(fn (SiteImage $record) => $record->currentUrl())
                    ->height(56),
                Tables\Columns\TextColumn::make('slot')->label(__('Name'))
                    ->formatStateUsing(fn (SiteImage $record) => $record->label())
                    ->description(fn (SiteImage $record) => $record->usedOn())
                    ->wrap(),
                Tables\Columns\TextColumn::make('size')->label(__('Recommended size'))
                    ->state(fn (SiteImage $record) => trim((SiteImage::SLOTS[$record->slot]['size'] ?? '').' '.(($ratio = SiteImage::SLOTS[$record->slot]['ratio'] ?? null) ? "({$ratio})" : ''))),
                Tables\Columns\TextColumn::make('source')->label(__('Source'))->badge()
                    ->state(fn (SiteImage $record) => $record->getFirstMedia('image') ? __('Uploaded') : __('Built-in'))
                    ->color(fn (SiteImage $record) => $record->getFirstMedia('image') ? 'success' : 'gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reset')
                    ->label(__('Use built-in image'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (SiteImage $record) => $record->getFirstMedia('image') !== null && (auth()->user()?->can('update', $record) ?? false))
                    ->action(function (SiteImage $record) {
                        $record->clearMediaCollection('image');
                        $record->touch();
                        Notification::make()->title(__('Built-in image restored'))->success()->send();
                    }),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('media'))
            ->defaultSort('id')
            ->paginated(false);
    }

    public static function missingAction(): Action
    {
        return Action::make('addMissing')
            ->label(__('Add missing image slots'))
            ->icon('heroicon-o-plus')
            ->visible(fn () => SiteImage::query()->count() < count(SiteImage::SLOTS) && (auth()->user()?->can('update', SiteImage::class) ?? false))
            ->action(function () {
                app(ImportSiteContent::class)->siteImages();
                Notification::make()->title(__('Image slots added'))->success()->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteImages::route('/'),
            'edit' => Pages\EditSiteImage::route('/{record}/edit'),
        ];
    }
}
