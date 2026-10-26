<?php

namespace App\Services;

use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;

/**
 * The downloadable stone catalogue (AR RTL + EN), generated with mPDF, which
 * shapes Arabic text properly. Files are cached under
 * storage/app/catalogue/ with a fingerprint of the stone data in the name, so
 * any change to stones, categories or attributes produces a new file; model
 * events (AppServiceProvider) also clear the folder.
 */
class CataloguePdf
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

            return $this->render($locale, collect([$stone->stoneCategory->setRelation('stoneTypes', collect([$stone]))]), $stone);
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

    private function fingerprint(): string
    {
        return substr(sha1(implode('|', [
            StoneType::max('updated_at'), StoneType::count(),
            StoneCategory::max('updated_at'), StoneCategory::count(),
            StoneAttribute::max('updated_at'), StoneAttribute::count(),
        ])), 0, 12);
    }

    private function categories()
    {
        return StoneCategory::ordered()
            ->with(['stoneTypes' => fn ($q) => $q->active()->ordered()->with(['stoneAttributes', 'media'])])
            ->get()
            ->filter(fn (StoneCategory $category) => $category->stoneTypes->isNotEmpty())
            ->values();
    }

    private function render(string $locale, $categories, ?StoneType $single): string
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            File::ensureDirectoryExists(storage_path('app/mpdf-tmp'));

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'tempDir' => storage_path('app/mpdf-tmp'),
                'default_font' => 'dejavusans',
                'margin_top' => 18,
                'margin_bottom' => 18,
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
            ]);
            $mpdf->SetDirectionality($locale === 'ar' ? 'rtl' : 'ltr');
            $mpdf->SetTitle(__('site.catalogue.title'));
            $mpdf->SetAuthor(__('site.meta.site_name'));
            // A phrase instead of "2 / 3": the slash would be reordered in RTL.
            $mpdf->SetFooter(__('site.meta.site_name').'||'.__('site.catalogue.page'));

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
