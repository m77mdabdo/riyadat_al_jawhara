<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-9';

    public function test_it_creates_an_admin_from_prompts(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Name', 'Owner')
            ->expectsQuestion('Email', 'owner@example.test')
            ->expectsQuestion('Password (min. 12 characters, letters and numbers)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD)
            ->assertSuccessful();

        $user = User::where('email', 'owner@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
        $this->assertNotSame(self::PASSWORD, $user->password);
    }

    public function test_created_admin_can_access_the_panel(): void
    {
        $this->artisan('app:create-admin', ['--name' => 'Owner', '--email' => 'owner@example.test'])
            ->expectsQuestion('Password (min. 12 characters, letters and numbers)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD)
            ->assertSuccessful();

        $this->actingAs(User::firstWhere('email', 'owner@example.test'))->get('/admin')->assertOk();
    }

    public function test_it_rejects_weak_or_mismatched_passwords(): void
    {
        $this->artisan('app:create-admin', ['--name' => 'Owner', '--email' => 'owner@example.test'])
            ->expectsQuestion('Password (min. 12 characters, letters and numbers)', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->assertFailed();

        $this->artisan('app:create-admin', ['--name' => 'Owner', '--email' => 'owner@example.test'])
            ->expectsQuestion('Password (min. 12 characters, letters and numbers)', self::PASSWORD)
            ->expectsQuestion('Confirm password', self::PASSWORD.'x')
            ->assertFailed();

        $this->assertSame(0, User::count());
    }

    public function test_it_rejects_invalid_or_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        foreach (['not-an-email', 'taken@example.test'] as $email) {
            $this->artisan('app:create-admin', ['--name' => 'Owner', '--email' => $email])
                ->expectsQuestion('Password (min. 12 characters, letters and numbers)', self::PASSWORD)
                ->expectsQuestion('Confirm password', self::PASSWORD)
                ->assertFailed();
        }

        $this->assertSame(1, User::count());
    }
}
