<?php

namespace App\Contracts;

use App\Models\StoneType;

/**
 * Builds the downloadable stone catalogue and per-stone spec sheets.
 * Implementations return the path of a (cached) PDF file.
 */
interface CatalogueGenerator
{
    public function catalogue(string $locale): string;

    public function specSheet(StoneType $stone, string $locale): string;

    /** Drops every cached file (called when anything printed in them changes). */
    public function flush(): void;
}
