<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Image\Image;

/**
 * Finds and downloads stock-photo *candidates* (with full attribution
 * metadata) for the site's static image slots, and scores them so one can be
 * picked automatically. Pexels and Pixabay only (licences allow commercial use
 * without attribution; we record credits anyway in docs/IMAGE_CREDITS.md).
 *
 * Dev/content tooling: never called while serving a page.
 */
class StockCandidateFinder
{
    /**
     * @return array<int, array<string, mixed>> candidates, best search rank first
     */
    public function search(string $query, int $count = 5, string $orientation = 'landscape'): array
    {
        return array_merge(
            $this->searchPexels($query, $count, $orientation),
            $this->searchPixabay($query, max(3, (int) ceil($count / 2)), $orientation),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function searchPexels(string $query, int $count, string $orientation): array
    {
        $key = config('services.pexels.key');
        if (! $key) {
            return [];
        }

        $response = Http::withHeaders(['Authorization' => $key])->get('https://api.pexels.com/v1/search', [
            'query' => $query,
            'per_page' => $count,
            'orientation' => $orientation,
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('photos', []))->values()->map(fn (array $photo, int $rank) => [
            'id' => 'pexels-'.$photo['id'],
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
        ])->filter(fn ($c) => $c['download_url'])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function searchPixabay(string $query, int $count, string $orientation): array
    {
        $key = config('services.pixabay.key');
        if (! $key) {
            return [];
        }

        $response = Http::get('https://pixabay.com/api/', [
            'key' => $key,
            'q' => Str::limit($query, 95, ''),
            'image_type' => 'photo',
            'orientation' => $orientation === 'landscape' ? 'horizontal' : 'all',
            'per_page' => max(3, $count),
            'safesearch' => 'true',
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('hits', []))->take($count)->values()->map(fn (array $hit, int $rank) => [
            'id' => 'pixabay-'.$hit['id'],
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
        ])->filter(fn ($c) => $c['download_url'])->values()->all();
    }

    /**
     * Downloads a candidate as WebP no wider than $maxWidth and no larger than
     * $maxKb, lowering quality step by step until it fits.
     */
    public function download(array $candidate, string $destination, int $maxWidth = 1920, int $maxKb = 250): ?string
    {
        $response = Http::timeout(60)->get($candidate['download_url']);
        if ($response->failed()) {
            return null;
        }

        $source = $destination.'.src';
        file_put_contents($source, $response->body());

        if (! @getimagesize($source)) {
            @unlink($source);

            return null;
        }

        foreach ([80, 72, 65, 58, 50, 42] as $quality) {
            $image = Image::load($source);
            if ($image->getWidth() > $maxWidth) {
                $image->width($maxWidth);
            }
            $image->format('webp')->quality($quality)->save($destination);

            clearstatcache(true, $destination);
            if (filesize($destination) <= $maxKb * 1024) {
                break;
            }
        }

        @unlink($source);

        if (filesize($destination) > $maxKb * 1024) {
            @unlink($destination); // over budget even at the lowest quality

            return null;
        }

        return $destination;
    }

    /**
     * Scores a downloaded candidate for the automatic pick. Higher is better.
     *
     * - relevance: search rank (earlier = more relevant to the query)
     * - brightness + warmth: the brief asks for bright, joyful, sunlit imagery
     * - calm area: low detail in the middle band leaves room for headline text
     * - resolution: enough pixels for the slot
     *
     * @return array{score: float, brightness: float, warmth: float, calm: float}
     */
    public function score(string $path, array $candidate, bool $needsTextSpace = false): array
    {
        $gd = @imagecreatefromwebp($path);
        if (! $gd) {
            return ['score' => 0, 'brightness' => 0, 'warmth' => 0, 'calm' => 0];
        }

        $w = imagesx($gd);
        $h = imagesy($gd);
        $small = imagescale($gd, 64, max(1, (int) round(64 * $h / $w)));
        $sw = imagesx($small);
        $sh = imagesy($small);

        $sumL = $sumWarm = 0;
        $n = 0;
        $midLums = [];
        for ($y = 0; $y < $sh; $y++) {
            for ($x = 0; $x < $sw; $x++) {
                $rgb = imagecolorat($small, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $l = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
                $sumL += $l;
                $sumWarm += ($r - $b);
                $n++;
                if ($y > $sh * 0.3 && $y < $sh * 0.7) {
                    $midLums[] = $l;
                }
            }
        }
        imagedestroy($small);
        imagedestroy($gd);

        $brightness = $sumL / $n / 255;                 // 0..1
        $warmth = max(-1, min(1, $sumWarm / $n / 80));  // ~-1..1
        $mean = array_sum($midLums) / max(1, count($midLums));
        $variance = array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $midLums)) / max(1, count($midLums));
        $calm = 1 - min(1, sqrt($variance) / 80);       // 1 = very uniform band

        $relevance = 1 - min(1, ($candidate['rank'] ?? 0) / 6);
        $resolution = min(1, (($candidate['width'] ?? $w) / 1920));
        // Prefer bright-but-not-blown-out images.
        $brightFit = 1 - abs($brightness - 0.62) / 0.62;

        $score = 0.35 * $relevance + 0.25 * $brightFit + 0.15 * (($warmth + 1) / 2) + 0.15 * $resolution
            + ($needsTextSpace ? 0.10 * $calm : 0.10 * $brightFit);

        return ['score' => round($score, 4), 'brightness' => round($brightness, 3), 'warmth' => round($warmth, 3), 'calm' => round($calm, 3)];
    }
}
