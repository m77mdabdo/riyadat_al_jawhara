<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The admin guide inside the panel (user menu → Help).
 */
class HelpPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_guide_opens_in_the_admins_language_with_its_screenshots(): void
    {
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $editor = tap(User::factory()->create(['locale' => 'ar']))->assignRole('editor');

        $html = $this->actingAs($editor)->get('/admin/help')->assertOk()->getContent();
        $this->assertStringContainsString('دليل لوحة التحكم', $html);
        $this->assertStringNotContainsString('Getting started', $html);
        $this->assertStringContainsString(route('admin.help.image', 'dashboard-ar.webp'), $html);
        $this->assertStringContainsString('/admin/help', $this->get('/admin')->getContent(), 'Help is in the user menu');

        $editor->update(['locale' => 'en']);
        $this->get('/admin/help')->assertOk()->assertSee('Getting started')->assertDontSee('البداية');
    }

    public function test_screenshots_are_only_for_signed_in_staff(): void
    {
        $file = collect(glob(base_path('docs/images/admin/*.webp')))->map(fn ($path) => basename($path))->first();
        $this->assertNotNull($file, 'guide screenshots exist');

        $this->get(route('admin.help.image', $file))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('admin.help.image', $file))->assertNotFound();

        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('editor'))
            ->get(route('admin.help.image', $file))->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->get('/admin-help/images/..%2F..%2F.env')->assertNotFound();
    }
}
