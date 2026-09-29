<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
    }

    public function test_core_public_pages_return_ok(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get('/en/services')->assertOk();
        $this->get('/en/stones')->assertOk();
        $this->get('/en/projects')->assertOk();
        $this->get('/en/about')->assertOk();
        $this->get('/en/contact')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
    }

    public function test_detail_pages_return_ok(): void
    {
        $service = Service::first();
        $stoneType = StoneType::first();
        $project = Project::first();

        $this->get("/en/services/{$service->slug}")->assertOk();
        $this->get("/en/stones/{$stoneType->slug}")->assertOk();
        $this->get("/en/projects/{$project->slug}")->assertOk();
    }

    public function test_arabic_pages_render_rtl(): void
    {
        $this->useLocale('ar');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
    }

    public function test_contact_form_requires_name_phone_and_message(): void
    {
        $response = $this->post('/en/contact', []);

        $response->assertSessionHasErrors(['name', 'phone', 'message']);
    }

    public function test_contact_form_stores_a_lead_on_valid_submission(): void
    {
        $response = $this->post('/en/contact', [
            'name' => 'Test User',
            'phone' => '0500000000',
            'message' => 'Test message content',
            'consent' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('leads', [
            'name' => 'Test User',
            'phone' => '0500000000',
        ]);
    }
}
