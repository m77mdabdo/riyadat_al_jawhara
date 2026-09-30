<?php

namespace App\Services\StockMedia;

use App\Contracts\StockMediaProvider;
use Illuminate\Support\Facades\Http;

class UnsplashProvider implements StockMediaProvider
{
    public function name(): string
    {
        return 'unsplash';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.unsplash.key'));
    }

    public function searchPhotos(string $query, int $count, ?string $orientation = null): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $response = Http::withHeaders(['Authorization' => 'Client-ID '.config('services.unsplash.key')])
            ->get('https://api.unsplash.com/search/photos', array_filter([
                'query' => $query,
                'per_page' => $count,
                'orientation' => $orientation,
            ]));

        if ($response->failed()) {
            return [];
        }

        return $response->collect('results')->values()->map(fn (array $photo, int $rank) => [
            'id' => 'unsplash-'.($photo['id'] ?? $rank),
            'source' => 'Unsplash',
            'rank' => $rank,
            'photographer' => $photo['user']['name'] ?? null,
            'photographer_url' => $photo['user']['links']['html'] ?? null,
            'page_url' => $photo['links']['html'] ?? null,
            'download_url' => $photo['urls']['regular'] ?? null,
            'width' => $photo['width'] ?? null,
            'height' => $photo['height'] ?? null,
            'alt' => $photo['alt_description'] ?? null,
            'license_url' => 'https://unsplash.com/license',
        ])->filter(fn (array $c) => $c['download_url'])->values()->all();
    }
}
