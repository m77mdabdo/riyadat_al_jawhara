<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_core_public_pages_return_ok(): void
    {
        $this->get('/')->assertOk();
        $this->get('/services')->assertOk();
        $this->get('/stones')->assertOk();
        $this->get('/projects')->assertOk();
        $this->get('/about')->assertOk();
        $this->get('/contact')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
    }

    public function test_detail_pages_return_ok(): void
    {
        $service = Service::first();
        $stoneType = StoneType::first();
        $project = Project::first();

        $this->get("/services/{$service->slug}")->assertOk();
        $this->get("/stones/{$stoneType->slug}")->assertOk();
        $this->get("/projects/{$project->slug}")->assertOk();
    }

    public function test_locale_switch_persists_across_requests(): void
    {
        $this->get('/lang/ar');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
    }

    public function test_contact_form_requires_name_phone_and_message(): void
    {
        $response = $this->post('/contact', []);

        $response->assertSessionHasErrors(['name', 'phone', 'message']);
    }

    public function test_contact_form_stores_a_lead_on_valid_submission(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'phone' => '0500000000',
            'message' => 'Test message content',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('leads', [
            'name' => 'Test User',
            'phone' => '0500000000',
        ]);
    }
}
