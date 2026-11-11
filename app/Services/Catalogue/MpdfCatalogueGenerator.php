<?php

namespace App\Services\Catalogue;

use App\Contracts\CatalogueGenerator;
use App\Models\Setting;
use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\File;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

/**
 * The downloadable stone catalogue (AR RTL + EN), generated with mPDF, which
 * shapes Arabic text properly. Files are cached under
 * storage/app/catalogue/ with a fingerprint of the stone data in the name, so
 * any change to stones, categories or attributes produces a new file; model
 * events (AppServiceProvider) also clear the folder.
 */
class MpdfCatalogueGenerator implements CatalogueGenerator
{
    public function directory(): string
    {
        return storage_path('app/catalogue');
    }

    /**
     * Path of the full catalogue PDF for $locale, generating it if needed.
     */
    public function catalogue(string $locale): string
    {
        return $this->cached("catalogue-{$locale}", fn () => $this->render($locale, $this->categories(), null));
    }

    /**
     * One-page spec sheet for a single stone.
     */
    public function specSheet(StoneType $stone, string $locale): string
    {
        return $this->cached("spec-{$stone->id}-{$locale}", function () use ($stone, $locale) {
            $stone->loadMissing(['stoneCategory', 'stoneAttributes', 'media']);

            return $this->render($locale, new EloquentCollection([$stone->stoneCategory->setRelation('stoneTypes', new EloquentCollection([$stone]))]), $stone);
        });
    }

    public function flush(): void
    {
        File::deleteDirectory($this->directory());
    }

    private function cached(string $name, callable $render): string
    {
        $path = $this->directory()."/{$name}-{$this->fingerprint()}.pdf";

        if (! is_file($path)) {
            File::ensureDirectoryExists($this->directory());
            // Drop older versions of this document before writing the new one.
            foreach (glob($this->directory()."/{$name}-*.pdf") as $old) {
                @unlink($old);
            }
            File::put($path, $render());
        }

        return $path;
    }

    /**
     * Changes whenever anything printed in the PDF changes: stone data, the
     * contact details from Settings, the templates or the translations (so a
     * deploy that edits the layout never serves an old cached file).
     */
    private function fingerprint(): string
    {
        $sources = [
            resource_path('views/pdf/catalogue.blade.php'),
            resource_path('views/pdf/footer.blade.php'),
            resource_path('views/pdf/partials/specs.blade.php'),
            lang_path('ar/site.php'),
            lang_path('en/site.php'),
        ];

        return substr(sha1(implode('|', [
            StoneType::max('updated_at'), StoneType::count(),
            StoneCategory::max('updated_at'), StoneCategory::count(),
            StoneAttribute::max('updated_at'), StoneAttribute::count(),
            Setting::current()->updated_at,
            ...array_map(fn (string $file) => is_file($file) ? sha1_file($file) : '', $sources),
        ])), 0, 12);
    }

    /**
     * @return EloquentCollection<int, StoneCategory>
     */
    private function categories(): EloquentCollection
    {
        return StoneCategory::ordered()
            ->with(['stoneTypes' => fn ($q) => $q->active()->ordered()->with(['stoneAttributes', 'media'])])
            ->get()
            ->filter(fn (StoneCategory $category) => $category->stoneTypes->isNotEmpty())
            ->values();
    }

    /**
     * @param  EloquentCollection<int, StoneCategory>  $categories
     */
    private function render(string $locale, EloquentCollection $categories, ?StoneType $single): string
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            File::ensureDirectoryExists(storage_path('app/mpdf-tmp'));

            $defaults = (new ConfigVariables)->getDefaults();
            $fonts = (new FontVariables)->getDefaults();

            // Brand fonts (SIL Open Font Licence, resources/fonts/pdf): Cairo for
            // text in both languages; Cairo Black (Arabic) and Marcellus (English)
            // for headings. Reem Kufi is not used: mPDF cannot read its GSUB tables.
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'tempDir' => storage_path('app/mpdf-tmp'),
                'fontDir' => [...$defaults['fontDir'], resource_path('fonts/pdf')],
                'fontdata' => $fonts['fontdata'] + [
                    'cairo' => ['R' => 'Cairo-Regular.ttf', 'B' => 'Cairo-Bold.ttf', 'useOTL' => 0xFF],
                    'cairoblack' => ['R' => 'Cairo-Black.ttf', 'B' => 'Cairo-Black.ttf', 'useOTL' => 0xFF],
                    'marcellus' => ['R' => 'Marcellus-Regular.ttf', 'B' => 'Marcellus-Regular.ttf'],
                ],
                'default_font' => 'cairo',
                'autoScriptToLang' => true,
                'autoLangToFont' => false,
            ]);
            $mpdf->SetDirectionality($locale === 'ar' ? 'rtl' : 'ltr');
            $mpdf->SetTitle(__('site.catalogue.title'));
            $mpdf->SetAuthor(__('site.meta.site_name'));
            // Header and footer are <htmlpageheader>/<htmlpagefooter> in the
            // template (none on the cover pages). The footer uses a phrase instead
            // of "2 / 3" because the slash would be reordered in RTL.

            $mpdf->WriteHTML(view('pdf.catalogue', [
                'categories' => $categories,
                'single' => $single,
                'locale' => $locale,
            ])->render());

            return $mpdf->Output('', 'S');
        } finally {
            app()->setLocale($previous);
        }
    }
}
