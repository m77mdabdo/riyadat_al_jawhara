<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Media Library writes to the "public" disk. Fake it for every test so
        // no test can ever write into the real storage/app/public folder.
        Storage::fake('public');

        // Tests don't depend on a compiled asset build (public/build is not
        // committed); CI builds the assets separately.
        $this->withoutVite();

        // Most tests were written against the English site; generate /en/...
        // URLs unless a test switches language with useLocale().
        $this->useLocale('en');
    }

    /**
     * Make route() generate public URLs in $locale (the visitor "chooses" a
     * language by the URL they visit; there is no session switch any more).
     */
    protected function useLocale(string $locale): static
    {
        URL::defaults(['locale' => $locale]);

        return $this;
    }
}
