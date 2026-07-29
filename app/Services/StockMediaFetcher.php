<?php

namespace App\Services;

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
     * @return array<int, string> local file paths (.webp)
     */
    public function fetchPhotos(string $query, string $source = 'pexels', int $count = 1): array
    {
        $urls = match ($source) {
            'pexels' => $this->searchPexelsPhotos($query, $count),
            'pixabay' => $this->searchPixabay($query, $count),
            'unsplash' => $this->searchUnsplash($query, $count),
            default => [],
        };

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
        $urls = $this->searchPexelsVideos($query, $count);
        $paths = [];

        foreach ($urls as $index => $url) {
            $path = $this->downloadVideo($url, $query, $index);

            if ($path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /** @return array<int, string> */
    private function searchPexelsPhotos(string $query, int $count): array
    {
        $key = env('PEXELS_API_KEY');

        if (! $key) {
            return [];
        }

        $response = Http::withHeaders(['Authorization' => $key])
            ->get('https://api.pexels.com/v1/search', [
                'query' => $query,
                'per_page' => $count,
            ]);

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('photos', []))
            ->pluck('src.large2x')
            ->filter()
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    private function searchPexelsVideos(string $query, int $count): array
    {
        $key = env('PEXELS_API_KEY');

        if (! $key) {
            return [];
        }

        $response = Http::withHeaders(['Authorization' => $key])
            ->get('https://api.pexels.com/videos/search', [
                'query' => $query,
                'per_page' => $count,
            ]);

        if ($response->failed()) {
            return [];
        }

        $urls = [];

        foreach ($response->json('videos', []) as $video) {
            $files = collect($video['video_files'] ?? []);

            $file = $files->firstWhere('quality', 'sd')
                ?? $files->where('quality', 'hd')->sortBy('width')->first()
                ?? $files->first();

            if ($file) {
                $urls[] = $file['link'];
            }
        }

        return $urls;
    }

    /** @return array<int, string> */
    private function searchPixabay(string $query, int $count): array
    {
        $key = env('PIXABAY_API_KEY');

        if (! $key) {
            return [];
        }

        $response = Http::get('https://pixabay.com/api/', [
            'key' => $key,
            'q' => $query,
            'image_type' => 'photo',
            'per_page' => max(3, $count), // Pixabay requires >= 3
            'safesearch' => 'true',
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('hits', []))
            ->take($count)
            ->pluck('largeImageURL')
            ->filter()
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    private function searchUnsplash(string $query, int $count): array
    {
        $key = env('UNSPLASH_ACCESS_KEY');

        if (! $key) {
            return [];
        }

        $response = Http::withHeaders(['Authorization' => "Client-ID {$key}"])
            ->get('https://api.unsplash.com/search/photos', [
                'query' => $query,
                'per_page' => $count,
            ]);

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('results', []))
            ->pluck('urls.regular')
            ->filter()
            ->values()
            ->all();
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
