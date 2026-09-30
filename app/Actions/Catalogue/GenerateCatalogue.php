<?php

namespace App\Actions\Catalogue;

use App\Contracts\CatalogueGenerator;
use App\Models\StoneType;

/**
 * Returns the PDF path and download name for the catalogue or one stone's
 * spec sheet in the given language.
 */
class GenerateCatalogue
{
    public function __construct(private CatalogueGenerator $generator) {}

    /**
     * @return array{path: string, filename: string}
     */
    public function handle(string $locale, ?StoneType $stone = null): array
    {
        return $stone
            ? ['path' => $this->generator->specSheet($stone, $locale), 'filename' => "{$stone->slug}-{$locale}.pdf"]
            : ['path' => $this->generator->catalogue($locale), 'filename' => "jra-stone-catalogue-{$locale}.pdf"];
    }
}
