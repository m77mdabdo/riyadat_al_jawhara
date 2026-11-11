<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use App\Notifications\StaffAccessNotification;
use App\Support\EmailPreviews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffInvitationTest extends TestCase
{
    use RefreshDatabase;

    private Role $editorRole;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->editorRole = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));
    }

    public function test_inviting_creates_the_account_without_a_known_password_and_emails_a_link(): void
    {
        Notification::fake();

        Livewire::test(CreateUser::class)
            ->assertFormSet(['send_invitation' => true])
            ->assertFormFieldIsHidden('password')
            ->fillForm(['name' => 'Huda', 'email' => 'huda@example.test', 'roles' => [$this->editorRole->id]])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'huda@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('editor'));
        $this->assertNotEmpty($user->password);

        Notification::assertSentTo($user, StaffAccessNotification::class, function (StaffAccessNotification $n) use ($user) {
            $mail = $n->toMail($user);

            return $n->invitation
                && str_contains($n->url, '/admin/password-reset/reset')
                && str_contains($n->url, urlencode($user->email))
                && $mail->actionUrl === $n->url;
        });
    }

    public function test_the_link_in_the_invitation_lets_the_person_choose_a_password(): void
    {
        Notification::fake();

        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Huda', 'email' => 'huda@example.test', 'roles' => [$this->editorRole->id]])
            ->call('create');

        $user = User::where('email', 'huda@example.test')->firstOrFail();
        $url = null;
        Notification::assertSentTo($user, StaffAccessNotification::class, function (StaffAccessNotification $n) use (&$url) {
            $url = $n->url;

            return true;
        });

        auth()->logout();
        $this->get($url)->assertOk();
    }

    public function test_without_invitation_the_admin_must_set_a_password(): void
    {
        Notification::fake();

        Livewire::test(CreateUser::class)
            ->fillForm(['send_invitation' => false, 'name' => 'Omar', 'email' => 'omar@example.test', 'roles' => [$this->editorRole->id]])
            ->call('create')
            ->assertHasFormErrors(['password' => 'required']);

        Livewire::test(CreateUser::class)
            ->fillForm(['send_invitation' => false, 'name' => 'Omar', 'email' => 'omar@example.test', 'password' => 'a-strong-password-123', 'roles' => [$this->editorRole->id]])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('a-strong-password-123', User::where('email', 'omar@example.test')->value('password')));
        Notification::assertNothingSent();
    }

    public function test_an_invitation_can_be_resent_from_the_list(): void
    {
        Notification::fake();
        $user = tap(User::factory()->create(['last_login_at' => null]))->assignRole('editor');

        Livewire::test(ListUsers::class)->callTableAction('sendAccessLink', $user);

        Notification::assertSentTo($user, StaffAccessNotification::class, fn (StaffAccessNotification $n) => $n->invitation);
    }

    public function test_invitation_email_names_the_role_in_both_languages(): void
    {
        $previews = app(EmailPreviews::class);

        $this->assertStringContainsString('Choose your password', $previews->html('staff_invitation', 'en'));
        $this->assertStringContainsString('اختيار كلمة المرور', $previews->html('staff_invitation', 'ar'));

        $user = tap(User::factory()->create())->assignRole('editor');
        $mail = (new StaffAccessNotification('https://example.test/x', true))->toMail($user->load('roles'));
        $this->assertStringContainsString(UserRole::Editor->getLabel(), implode(' ', $mail->introLines));
        $this->assertStringContainsString($user->email, implode(' ', $mail->introLines));
    }
}
