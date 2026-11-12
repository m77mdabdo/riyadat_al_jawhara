<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\StaffAccessNotification;
use App\Providers\AppServiceProvider;
use App\Support\StaffAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Password-reset and invitation links must point at APP_URL whatever Host
 * header the request carried (host-header poisoning).
 */
class HostHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // The trusted-host list is static on Symfony's Request.
        Request::setTrustedHosts([]);

        parent::tearDown();
    }

    /** Boot as on the live server: production, with the real APP_URL. */
    private function production(): void
    {
        config(['app.url' => 'https://stone.example.sa']);
        $this->app->detectEnvironment(fn () => 'production');
        $this->app->getProvider(AppServiceProvider::class)->boot();
    }

    public function test_a_forged_host_does_not_change_the_password_link(): void
    {
        Notification::fake();
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $user = tap(User::factory()->create(['last_login_at' => now()]))->assignRole('editor');
        $this->production();

        // The request that triggers the email arrived with an attacker's Host.
        $forged = Request::create('http://attacker.test/admin/users', 'POST');
        $this->app->instance('request', $forged);
        $this->app['url']->setRequest($forged);

        $this->assertTrue(StaffAccess::send($user, invitation: false));

        Notification::assertSentTo($user, StaffAccessNotification::class, function (StaffAccessNotification $n) {
            return str_starts_with($n->url, 'https://stone.example.sa/admin/password-reset/reset')
                && ! str_contains($n->url, 'attacker.test');
        });
    }

    public function test_requests_for_another_host_are_refused_in_production(): void
    {
        $this->production();

        $this->get('https://stone.example.sa/admin/login')->assertOk();
        $this->get('https://attacker.test/admin/login')->assertStatus(400);
    }
}
