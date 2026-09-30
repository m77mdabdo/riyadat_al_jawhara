<?php

namespace App\Services;

use App\Contracts\StockMediaProvider;
use App\Services\StockMedia\PexelsProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Image\Image;

/**
 * Searches Pexels/Pixabay/Unsplash and saves optimized local copies for
 * Media Library to attach. Used by both the fetch:stock-media command
 * (manual/CLI use) and seeders (programmatic use, so paths can be piped
 * straight into addMedia()->toMediaCollection()).
 *
 * Pexels and Pixabay photos/videos are free for commercial use without
 * attribution under their current license terms (checked 2026-07). Verify
 * this hasn't changed if licensing is ever audited.
 */
class StockMediaFetcher
{
    private const MAX_WIDTH = 1920;

    private const QUALITY = 80;

    /**
     * @param  iterable<StockMediaProvider>  $providers
     */
    public function __construct(private iterable $providers, private PexelsProvider $pexels) {}

    /**
     * @return array<int, string> local file paths (.webp)
     */
    public function fetchPhotos(string $query, string $source = 'pexels', int $count = 1): array
    {
        $provider = collect($this->providers)->first(fn (StockMediaProvider $p) => $p->name() === $source);
        $urls = $provider ? array_column($provider->searchPhotos($query, $count), 'download_url') : [];

        $paths = [];

        foreach ($urls as $index => $url) {
            $path = $this->downloadAndOptimizePhoto($url, $query, $index);

            if ($path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @return array<int, string> local file paths (.mp4, uncompressed -- caller should re-encode with ffmpeg)
     */
    public function fetchVideos(string $query, int $count = 1): array
    {
        $urls = $this->pexels->searchVideoUrls($query, $count);
        $paths = [];

        foreach ($urls as $index => $url) {
            $path = $this->downloadVideo($url, $query, $index);

            if ($path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function downloadAndOptimizePhoto(string $url, string $query, int $index): ?string
    {
        $response = Http::get($url);

        if ($response->failed()) {
            return null;
        }

        $tmpDir = storage_path('app/stock-media-tmp');

        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $sourcePath = $tmpDir.'/'.Str::uuid().'.tmp';
        file_put_contents($sourcePath, $response->body());

        $slug = Str::slug($query);
        $destination = "{$tmpDir}/{$slug}-{$index}-".Str::random(6).'.webp';

        $image = Image::load($sourcePath);

        [$sourceWidth] = getimagesize($sourcePath);

        if ($sourceWidth > self::MAX_WIDTH) {
            $image->width(self::MAX_WIDTH);
        }

        $image->format('webp')
            ->quality(self::QUALITY)
            ->save($destination);

        unlink($sourcePath);

        return $destination;
    }

    private function downloadVideo(string $url, string $query, int $index): ?string
    {
        $response = Http::get($url);

        if ($response->failed()) {
            return null;
        }

        $tmpDir = storage_path('app/stock-media-tmp');

        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $slug = Str::slug($query);
        $destination = "{$tmpDir}/{$slug}-{$index}.mp4";

        file_put_contents($destination, $response->body());

        return $destination;
    }
}
