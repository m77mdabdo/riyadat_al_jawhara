<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeder_creates_roles_only(): void
    {
        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class); // idempotent

        $this->assertEqualsCanonicalizing(['admin', 'editor'], Role::pluck('name')->all());
        $this->assertSame(0, User::count());
        $this->assertSame(0, Service::count());
        $this->assertSame(0, Lead::count());
    }

    public function test_default_database_seeder_is_production_safe(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, User::count());
        $this->assertSame(0, Service::count());
        $this->assertSame(2, Role::count());
    }

    public function test_demo_seeder_creates_content_but_no_users(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertSame(0, User::count());
        $this->assertGreaterThan(0, Service::count());
        $this->assertGreaterThan(0, Post::count());
        $this->assertNull(Post::first()->author_id);
    }

    public function test_no_seeder_contains_a_hard_coded_password(): void
    {
        foreach (glob(database_path('seeders/*.php')) as $file) {
            $source = file_get_contents($file);
            $this->assertDoesNotMatchRegularExpression('/bcrypt\(|Hash::make\(|[\'"]password[\'"]\s*=>/', $source, basename($file));
        }
    }
}
