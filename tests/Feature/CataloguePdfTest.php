<?php

namespace Tests\Feature;

use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Services\CataloguePdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CataloguePdfTest extends TestCase
{
    use RefreshDatabase;

    private string $storage;

    protected function setUp(): void
    {
        parent::setUp();

        // Generated PDFs go to a throwaway storage path, never the real one.
        $this->storage = sys_get_temp_dir().'/jra-pdf-'.uniqid();
        $this->app->useStoragePath($this->storage);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->storage);
        parent::tearDown();
    }

    private function catalogueHtml(string $locale): string
    {
        app()->setLocale($locale);
        $categories = StoneCategory::ordered()->with(['stoneTypes' => fn ($q) => $q->active()->ordered()])->get()->filter(fn ($c) => $c->stoneTypes->isNotEmpty());

        return view('pdf.catalogue', ['categories' => $categories, 'single' => null, 'locale' => $locale])->render();
    }

    public function test_catalogue_pdf_downloads_in_both_languages(): void
    {
        $stone = StoneType::factory()->create(['name_en' => 'Carrara', 'name_ar' => 'كرارا']);
        $stone->addMedia(UploadedFile::fake()->image('c.jpg', 800, 600))->toMediaCollection('cover');

        foreach (['en', 'ar'] as $locale) {
            $this->useLocale($locale);
            $response = $this->get(route('stones.catalogue'))->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF', file_get_contents($response->getFile()->getPathname()));
            $this->assertStringContainsString("jra-stone-catalogue-{$locale}.pdf", $response->headers->get('Content-Disposition'));
            $response->assertHeaderMissing('Content-Security-Policy')->assertHeader('X-Content-Type-Options', 'nosniff');
        }
    }

    public function test_catalogue_lists_only_active_stones_with_rtl_for_arabic(): void
    {
        StoneType::factory()->create(['name_en' => 'Visible Stone', 'name_ar' => 'حجر ظاهر']);
        StoneType::factory()->inactive()->create(['name_en' => 'Hidden Stone']);

        $en = $this->catalogueHtml('en');
        $this->assertStringContainsString('Visible Stone', $en);
        $this->assertStringNotContainsString('Hidden Stone', $en);
        $this->assertStringContainsString('dir="ltr"', $en);

        $ar = $this->catalogueHtml('ar');
        $this->assertStringContainsString('حجر ظاهر', $ar);
        $this->assertStringContainsString('dir="rtl"', $ar);
    }

    public function test_pdf_is_cached_and_regenerated_when_stones_change(): void
    {
        $stone = StoneType::factory()->create();
        $pdf = app(CataloguePdf::class);

        $first = $pdf->catalogue('en');
        $this->assertSame($first, $pdf->catalogue('en'), 'second call reuses the cached file');

        $this->travel(2)->seconds();
        $stone->update(['name_en' => 'Renamed']);

        $this->assertFileDoesNotExist($first, 'model events clear stale files');
        $second = $pdf->catalogue('en');
        $this->assertNotSame($first, $second);
        $this->assertCount(1, glob($pdf->directory().'/catalogue-en-*.pdf'));
    }

    public function test_spec_sheet_for_active_stone_only(): void
    {
        $stone = StoneType::factory()->create();
        $hidden = StoneType::factory()->inactive()->create();

        $this->get(route('stones.spec-sheet', $stone))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get(route('stones.spec-sheet', $hidden))->assertNotFound();
    }

    public function test_download_ctas_are_on_the_stone_pages(): void
    {
        $stone = StoneType::factory()->create();

        $this->get(route('stones.index'))->assertSee(route('stones.catalogue'), false)->assertSee(__('site.catalogue.download'));
        $this->get(route('stones.show', $stone))->assertSee(route('stones.spec-sheet', $stone), false);
    }
}
