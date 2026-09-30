<?php

namespace App\Contracts;

/**
 * A stock-photo API (dev/demo tooling only; never called while serving a page).
 *
 * searchPhotos() returns normalised candidates:
 * id, source, rank, photographer, photographer_url, page_url, download_url,
 * width, height, alt, license_url.
 */
interface StockMediaProvider
{
    /** Short key used on the command line, e.g. "pexels". */
    public function name(): string;

    /** False when no API key is configured (the provider then makes no request). */
    public function isConfigured(): bool;

    /**
     * @param  string|null  $orientation  "landscape" / "portrait", or null for the API default
     * @return list<array<string, mixed>>
     */
    public function searchPhotos(string $query, int $count, ?string $orientation = null): array;
}
