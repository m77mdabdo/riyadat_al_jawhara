<?php

namespace Tests\Unit;

use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * Every Arabic translation file has exactly the same keys as its English
 * counterpart (and the other way round), so no string is missing in either
 * language.
 */
class TranslationParityTest extends TestCase
{
    public function test_arabic_and_english_files_have_the_same_keys(): void
    {
        $files = collect(glob(lang_path('en/*.php')))->map(fn (string $path) => basename($path))
            ->merge(collect(glob(lang_path('ar/*.php')))->map(fn (string $path) => basename($path)))
            ->unique();

        foreach ($files as $file) {
            $this->assertFileExists(lang_path("en/{$file}"));
            $this->assertFileExists(lang_path("ar/{$file}"));

            $en = array_keys(Arr::dot(require lang_path("en/{$file}")));
            $ar = array_keys(Arr::dot(require lang_path("ar/{$file}")));

            $this->assertSame([], array_values(array_diff($en, $ar)), "keys missing in ar/{$file}");
            $this->assertSame([], array_values(array_diff($ar, $en)), "keys missing in en/{$file}");
        }
    }
}
