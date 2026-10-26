<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestIsolationTest extends TestCase
{
    public function test_public_disk_is_faked_for_every_test(): void
    {
        Storage::disk('public')->putFileAs('isolation-check', UploadedFile::fake()->image('x.jpg'), 'x.jpg');

        $this->assertFileDoesNotExist(storage_path('app/public/isolation-check/x.jpg'));
        Storage::disk('public')->assertExists('isolation-check/x.jpg');
    }
}
