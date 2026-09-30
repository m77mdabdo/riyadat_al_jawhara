<?php

namespace App\Services\StockMedia;

use App\Contracts\StockMediaProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class PexelsProvider implements StockMediaProvider
{
    public function name(): string
    {
        return 'pexels';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.pexels.key'));
    }

    public function searchPhotos(string $query, int $count, ?string $orientation = null): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $response = Http::withHeaders(['Authorization' => config('services.pexels.key')])
            ->get('https://api.pexels.com/v1/search', array_filter([
                'query' => $query,
                'per_page' => $count,
                'orientation' => $orientation,
            ]));

        if ($response->failed()) {
            return [];
        }

        return $response->collect('photos')->values()->map(fn (array $photo, int $rank) => [
            'id' => 'pexels-'.($photo['id'] ?? $rank),
            'source' => 'Pexels',
            'rank' => $rank,
            'photographer' => $photo['photographer'] ?? null,
            'photographer_url' => $photo['photographer_url'] ?? null,
            'page_url' => $photo['url'] ?? null,
            'download_url' => $photo['src']['large2x'] ?? $photo['src']['original'] ?? null,
            'width' => $photo['width'] ?? null,
            'height' => $photo['height'] ?? null,
            'alt' => $photo['alt'] ?? null,
            'license_url' => 'https://www.pexels.com/license/',
        ])->filter(fn (array $c) => $c['download_url'])->values()->all();
    }

    /**
     * Download URLs of short stock videos (SD preferred, to keep files small).
     *
     * @return list<string>
     */
    public function searchVideoUrls(string $query, int $count): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $response = Http::withHeaders(['Authorization' => config('services.pexels.key')])
            ->get('https://api.pexels.com/videos/search', ['query' => $query, 'per_page' => $count]);

        if ($response->failed()) {
            return [];
        }

        $urls = [];
        foreach ($response->collect('videos') as $video) {
            $files = collect(Arr::wrap($video['video_files'] ?? []));
            $file = $files->firstWhere('quality', 'sd')
                ?? $files->where('quality', 'hd')->sortBy('width')->first()
                ?? $files->first();

            if ($file) {
                $urls[] = $file['link'];
            }
        }

        return $urls;
    }
}
