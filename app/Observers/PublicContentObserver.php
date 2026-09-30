<?php

namespace App\Observers;

use App\Contracts\CatalogueGenerator;
use App\Models\Setting;
use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Support\FragmentCache;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Invalidates cached public output when content changes: the rendered home
 * sections and sitemap (FragmentCache), and the catalogue PDFs when anything
 * printed in them changes (stone data, contact details, images).
 */
class PublicContentObserver
{
    /** Models whose data is printed in the catalogue PDFs. */
    private const CATALOGUE_MODELS = [StoneType::class, StoneCategory::class, StoneAttribute::class, Setting::class, Media::class];

    public function __construct(private CatalogueGenerator $catalogue) {}

    public function saved(Model $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(Model $model): void
    {
        FragmentCache::flush();

        if (in_array($model::class, self::CATALOGUE_MODELS, true)) {
            $this->catalogue->flush();
        }
    }
}
