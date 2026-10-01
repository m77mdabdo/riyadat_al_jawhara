<?php

namespace App\Observers;

use App\Contracts\CatalogueGenerator;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Setting;
use App\Models\SiteImage;
use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Support\FragmentCache;
use App\Support\Navigation;
use App\Support\PageContent;
use App\Support\SiteImages;
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

        // Uploaded site images and page banners are media files.
        if ($model instanceof Media && $model->model_type === (new SiteImage)->getMorphClass()) {
            SiteImages::flush();
        }
        if ($model instanceof Media && $model->model_type === (new Page)->getMorphClass()) {
            PageContent::flush();
        }

        if ($model instanceof Menu || $model instanceof MenuItem) {
            Navigation::flush();
        } elseif (in_array($model::class, Navigation::LINKABLE, true)) {
            Navigation::flushIfLinked($model);
        }

        if (in_array($model::class, self::CATALOGUE_MODELS, true)) {
            $this->catalogue->flush();
        }
    }
}
