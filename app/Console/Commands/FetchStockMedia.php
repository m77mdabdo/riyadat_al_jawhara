<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Image\Image;

class FetchStockMedia extends Command
{
    protected $signature = 'fetch:stock-media
        {query : Search term, e.g. "marble texture"}
        {--source=pexels : pexels, pixabay, or unsplash}
        {--count=1 : how many results to download}
        {--video : fetch a video instead of a photo (pexels only)}';

    protected $description = 'Search Pexels/Pixabay/Unsplash for stock media and save optimized local copies';

    private const MAX_WIDTH = 1920;

    private const QUALITY = 80;

    public function handle(): int
    {
        $query = $this->argument('query');
        $source = $this->option('source');
        $count = max(1, (int) $this->option('count'));
        $isVideo = (bool) $this->option('video');

        $items = match (true) {
            $isVideo && $source === 'pexels' => $this->searchPexelsVideos($query, $count),
            $source === 'pexels' => $this->searchPexelsPhotos($query, $count),
            $source === 'pixabay' => $this->searchPixabay($query, $count),
            $source === 'unsplash' => $this->searchUnsplash($query, $count),
            default => null,
        };

        if ($items === null) {
            $this->error("Unknown source \"{$source}\". Use pexels, pixabay, or unsplash.");

            return self::FAILURE;
        }

        if ($items === []) {
            $this->error("No results for \"{$query}\" via {$source}. Check the API key and query.");

            return self::FAILURE;
        }

        $saved = [];

        foreach ($items as $index => $url) {
            $path = $isVideo
                ? $this->downloadVideo($url, $query, $index)
                : $this->downloadAndOptimizePhoto($url, $query, $index);

            if ($path) {
                $saved[] = $path;
                $sizeKb = round(filesize($path) / 1024);
                $this->info("Saved: {$path} ({$sizeKb} KB)");
            }
        }

        if ($saved === []) {
            $this->error('Found results but failed to download/process any of them.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /** @return array<int, string> */
    private function searchPexelsPhotos(string $query, int $count): array
    {
        $key = env('PEXELS_API_KEY');

        if (! $key) {
            $this->error('PEXELS_API_KEY is not set in .env.');

            return [];
        }

        $response = Http::withHeaders(['Authorization' => $key])
            ->get('https://api.pexels.com/v1/search', [
                'query' => $query,
                'per_page' => $count,
            ]);

        if ($response->failed()) {
            $this->error("Pexels API error ({$response->status()}): {$response->body()}");

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
            $this->error('PEXELS_API_KEY is not set in .env.');

            return [];
        }

        $response = Http::withHeaders(['Authorization' => $key])
            ->get('https://api.pexels.com/videos/search', [
                'query' => $query,
                'per_page' => $count,
            ]);

        if ($response->failed()) {
            $this->error("Pexels API error ({$response->status()}): {$response->body()}");

            return [];
        }

        $urls = [];

        foreach ($response->json('videos', []) as $video) {
            $files = collect($video['video_files'] ?? []);

            // Prefer the smallest usable file: "sd" quality if present, otherwise the narrowest "hd".
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
            $this->error('PIXABAY_API_KEY is not set in .env.');

            return [];
        }

        // Pixabay requires per_page to be at least 3.
        $response = Http::get('https://pixabay.com/api/', [
            'key' => $key,
            'q' => $query,
            'image_type' => 'photo',
            'per_page' => max(3, $count),
            'safesearch' => 'true',
        ]);

        if ($response->failed()) {
            $this->error("Pixabay API error ({$response->status()}): {$response->body()}");

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
            $this->error('UNSPLASH_ACCESS_KEY is not set in .env.');

            return [];
        }

        $response = Http::withHeaders(['Authorization' => "Client-ID {$key}"])
            ->get('https://api.unsplash.com/search/photos', [
                'query' => $query,
                'per_page' => $count,
            ]);

        if ($response->failed()) {
            $this->error("Unsplash API error ({$response->status()}): {$response->body()}");

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
            $this->error("Failed to download {$url} ({$response->status()})");

            return null;
        }

        $tmpDir = storage_path('app/stock-media-tmp');

        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $sourcePath = $tmpDir.'/'.Str::uuid().'.tmp';
        file_put_contents($sourcePath, $response->body());

        $slug = Str::slug($query);
        $destination = "{$tmpDir}/{$slug}-{$index}.webp";

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
            $this->error("Failed to download {$url} ({$response->status()})");

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
