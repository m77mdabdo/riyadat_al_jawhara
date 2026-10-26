<?php

namespace Tests\Unit;

use Tests\TestCase;

class FrontendDependenciesTest extends TestCase
{
    public function test_axios_is_no_longer_a_dependency_or_imported(): void
    {
        $package = json_decode(file_get_contents(base_path('package.json')), true);

        $this->assertArrayNotHasKey('axios', $package['dependencies'] ?? []);
        $this->assertArrayNotHasKey('axios', $package['devDependencies'] ?? []);
        $this->assertStringNotContainsString('axios', file_get_contents(resource_path('js/app.js')));
        $this->assertStringNotContainsString('import', file_get_contents(resource_path('js/bootstrap.js')));
    }

    public function test_framework_json_strings_are_translated_in_arabic(): void
    {
        app()->setLocale('ar');

        foreach (['Pagination Navigation', 'Showing', 'to', 'of', 'results'] as $key) {
            $this->assertNotSame($key, __($key), $key);
        }
        $this->assertSame('الانتقال إلى الصفحة 3', __('Go to page :page', ['page' => 3]));
    }
}
