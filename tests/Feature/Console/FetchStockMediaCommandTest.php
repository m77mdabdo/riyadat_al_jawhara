<?php

namespace Tests\Feature\Console;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchStockMediaCommandTest extends TestCase
{
    private string $storage;

    protected function setUp(): void
    {
        parent::setUp();

        // The fetcher writes to storage_path(); point it at a throwaway dir.
        $this->storage = sys_get_temp_dir().'/jra-stock-'.uniqid();
        $this->app->useStoragePath($this->storage);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->storage);

        parent::tearDown();
    }

    public function test_it_downloads_and_optimizes_a_photo(): void
    {
        config(['services.pexels.key' => 'test-key']);
        $image = UploadedFile::fake()->image('marble.jpg', 2400, 1600);
        $jpeg = file_get_contents($image->getRealPath());

        Http::fake([
            'api.pexels.com/*' => Http::response(['photos' => [['src' => ['large2x' => 'https://images.pexels.test/marble.jpg']]]]),
            'images.pexels.test/*' => Http::response($jpeg, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->artisan('fetch:stock-media', ['query' => 'marble texture'])->assertSuccessful();

        $files = glob($this->storage.'/app/stock-media-tmp/*.webp');
        $this->assertCount(1, $files);
        $this->assertLessThanOrEqual(1920, getimagesize($files[0])[0]);
        $this->assertDirectoryDoesNotExist(base_path('storage/app/stock-media-tmp/'.basename($files[0])));
    }

    public function test_it_fails_cleanly_without_an_api_key(): void
    {
        config(['services.pexels.key' => null]);
        Http::fake();

        $this->artisan('fetch:stock-media', ['query' => 'marble'])->assertFailed();
        Http::assertNothingSent();
    }
}
