<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Models\Service;
use App\Models\User;
use App\Support\ServiceIcon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceIconTest extends TestCase
{
    use RefreshDatabase;

    private function service(?string $icon): Service
    {
        return Service::create([
            'name_en' => 'Marble Installation',
            'name_ar' => 'تركيب الرخام',
            'icon' => $icon,
            'is_active' => true,
        ]);
    }

    public function test_invalid_stored_icon_does_not_break_any_service_page(): void
    {
        $service = $this->service('heroicon-o-does-not-exist');

        $this->get(route('home'))->assertOk();
        $this->get(route('services.index'))->assertOk();
        $this->get(route('services.show', $service))->assertOk();
    }

    public function test_empty_icon_renders_default(): void
    {
        $service = $this->service(null);

        $this->assertSame(ServiceIcon::DEFAULT, $service->safe_icon);
        $this->get(route('services.show', $service))->assertOk();
    }

    public function test_allowed_icon_is_kept(): void
    {
        $this->assertSame('heroicon-o-truck', $this->service('heroicon-o-truck')->safe_icon);
        $this->assertSame(ServiceIcon::DEFAULT, $this->service('<script>')->safe_icon);
    }

    public function test_every_allowed_icon_renders(): void
    {
        foreach (array_keys(ServiceIcon::OPTIONS) as $icon) {
            $service = Service::create(['name_en' => "S {$icon}", 'name_ar' => 'خدمة', 'icon' => $icon, 'is_active' => true]);
            $this->get(route('services.show', $service))->assertOk();
        }
    }

    public function test_admin_form_rejects_icons_outside_the_list(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        Livewire::test(CreateService::class)
            ->fillForm([
                'name_en' => 'Cladding',
                'name_ar' => 'تكسية',
                'slug' => 'cladding',
                'icon' => 'heroicon-o-does-not-exist',
            ])
            ->call('create')
            ->assertHasFormErrors(['icon']);

        $this->assertDatabaseMissing('services', ['slug' => 'cladding']);
    }
}
