<?php

namespace App\Services;

use App\Contracts\StockMediaProvider;
use Illuminate\Support\Facades\Http;
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
     * @param  list<StockMediaProvider>  $providers  Primary provider first
     */
    public function __construct(private array $providers) {}

    /**
     * The primary provider is asked for $count results, the others for about
     * half as many, so the review page stays focused on the best source.
     *
     * @return array<int, array<string, mixed>> candidates, best search rank first
     */
    public function search(string $query, int $count = 5, string $orientation = 'landscape'): array
    {
        $candidates = [];
        foreach ($this->providers as $index => $provider) {
            $wanted = $index === 0 ? $count : max(3, (int) ceil($count / 2));
            $candidates = array_merge($candidates, $provider->searchPhotos($query, $wanted, $orientation));
        }

        return $candidates;
    }

    /**
     * Downloads a candidate as WebP no wider than $maxWidth and no larger than
     * $maxKb, lowering quality step by step until it fits.
     */
    /**
     * @param  array<string, mixed>  $candidate
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
     * @param  array<string, mixed>  $candidate
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
