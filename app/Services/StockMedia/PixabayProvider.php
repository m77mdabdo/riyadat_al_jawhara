<?php

namespace App\Services\StockMedia;

use App\Contracts\StockMediaProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PixabayProvider implements StockMediaProvider
{
    public function name(): string
    {
        return 'pixabay';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.pixabay.key'));
    }

    public function searchPhotos(string $query, int $count, ?string $orientation = null): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $response = Http::get('https://pixabay.com/api/', array_filter([
            'key' => config('services.pixabay.key'),
            'q' => Str::limit($query, 95, ''), // the API rejects queries over 100 characters
            'image_type' => 'photo',
            'orientation' => match ($orientation) {
                null => null,
                'landscape' => 'horizontal',
                default => 'all',
            },
            'per_page' => max(3, $count), // the API requires at least 3
            'safesearch' => 'true',
        ]));

        if ($response->failed()) {
            return [];
        }

        return $response->collect('hits')->take($count)->values()->map(fn (array $hit, int $rank) => [
            'id' => 'pixabay-'.($hit['id'] ?? $rank),
            'source' => 'Pixabay',
            'rank' => $rank,
            'photographer' => $hit['user'] ?? null,
            'photographer_url' => isset($hit['user'], $hit['user_id']) ? "https://pixabay.com/users/{$hit['user']}-{$hit['user_id']}/" : null,
            'page_url' => $hit['pageURL'] ?? null,
            'download_url' => $hit['largeImageURL'] ?? null,
            'width' => $hit['imageWidth'] ?? null,
            'height' => $hit['imageHeight'] ?? null,
            'alt' => $hit['tags'] ?? null,
            'license_url' => 'https://pixabay.com/service/license-summary/',
        ])->filter(fn (array $c) => $c['download_url'])->values()->all();
    }
}
