<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Settings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function userWithRole(string $role): User
    {
        return tap(User::factory()->create())->assignRole($role);
    }

    public function test_admin_can_open_and_save_settings(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $this->get('/admin/settings')->assertOk();

        Livewire::test(Settings::class)
            ->set('data.phone', '+966 11 000 0000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('+966 11 000 0000', Setting::current()->phone);
    }

    public function test_editor_is_forbidden_from_settings_page(): void
    {
        $this->actingAs($this->userWithRole('editor'))
            ->get('/admin/settings')
            ->assertForbidden();
    }

    public function test_editor_cannot_save_settings_through_livewire(): void
    {
        $this->actingAs($this->userWithRole('editor'));

        Livewire::test(Settings::class)->assertForbidden();
    }

    public function test_settings_is_hidden_from_editor_navigation(): void
    {
        $this->actingAs($this->userWithRole('editor'));

        $this->assertFalse(Settings::canAccess());
        $this->get('/admin')->assertOk()->assertDontSee('/admin/settings', false);
    }
}
