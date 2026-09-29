<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

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
    }
}
