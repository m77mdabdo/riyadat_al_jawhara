<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

/**
 * The only way admin forms accept images: JPG, PNG or WebP (no SVG, which
 * could carry scripts and is served from the site's own origin) and a size
 * cap. Models enforce the same MIME list on their media collections.
 *
 * make(..., size: '1920×1080', ratio: '16:9') adds the recommended size to the
 * helper text and an image editor with that crop preset.
 */
class ImageUpload
{
    public const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Upload cap in kilobytes (config/site.php). */
    public static function maxKb(): int
    {
        return (int) config('site.uploads.max_kb');
    }

    public static function make(string $name, ?string $collection = null, ?string $size = null, ?string $ratio = null): SpatieMediaLibraryFileUpload
    {
        $limits = __('JPG, PNG or WebP, up to :size MB.', ['size' => round(self::maxKb() / 1024)]);

        $field = SpatieMediaLibraryFileUpload::make($name)
            ->collection($collection ?? $name)
            ->image()
            ->acceptedFileTypes(self::MIME_TYPES)
            ->maxSize(self::maxKb())
            ->helperText($size ? __('Recommended :size (:ratio).', ['size' => $size, 'ratio' => $ratio ?? '—']).' '.$limits : $limits);

        if ($ratio) {
            $field->imageEditor()->imageEditorAspectRatios([$ratio, null]);
        }

        return $field;
    }

    /**
     * Alt text (AR/EN) stored on the collection's first image as custom
     * properties (alt_ar / alt_en). Place it after the upload field so the
     * image is saved first.
     */
    public static function altFields(string $collection): Grid
    {
        $field = fn (string $locale) => TextInput::make("{$collection}_alt_{$locale}")
            ->label(__('Image description (:language)', ['language' => strtoupper($locale)]))
            ->helperText($locale === 'ar' ? __('For screen readers and search engines. Leave empty to use the title.') : null)
            ->maxLength(200)
            ->dehydrated(false)
            ->afterStateHydrated(function (TextInput $component, ?Model $record) use ($collection, $locale) {
                if ($record instanceof HasMedia) {
                    $component->state($record->getMedia($collection)->first()?->getCustomProperty("alt_{$locale}"));
                }
            })
            ->saveRelationshipsUsing(function (?Model $record, ?string $state) use ($collection, $locale) {
                $media = $record instanceof HasMedia ? $record->getMedia($collection)->first() : null;
                if ($media) {
                    $media->setCustomProperty("alt_{$locale}", filled($state) ? trim($state) : null)->save();
                }
            });

        return Grid::make(2)->schema([$field('ar'), $field('en')]);
    }
}
