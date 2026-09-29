<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\User;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    private const OLD = 'old-password-1234';

    private const NEW = 'new-password-5678';

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $this->editor = tap(User::factory()->create(['email' => 'editor@example.test', 'password' => self::OLD]))->assignRole('editor');
    }

    public function test_password_reset_request_page_is_available_and_linked_from_login(): void
    {
        $this->get('/admin/password-reset/request')->assertOk();
        $this->get('/admin/login')->assertOk()->assertSee('/admin/password-reset/request', false);
    }

    public function test_requesting_a_reset_emails_the_user(): void
    {
        Notification::fake();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'editor@example.test'])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertSentTo($this->editor, ResetPassword::class);
    }

    public function test_reset_request_for_unknown_email_sends_nothing(): void
    {
        Notification::fake();

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'nobody@example.test'])
            ->call('request');

        Notification::assertNothingSent();
    }

    public function test_staff_can_open_their_profile(): void
    {
        $this->actingAs($this->editor)->get('/admin/profile')->assertOk();
    }

    public function test_changing_password_requires_the_current_password(): void
    {
        $this->actingAs($this->editor);

        Livewire::test(EditProfile::class)
            ->fillForm(['password' => self::NEW, 'passwordConfirmation' => self::NEW])
            ->call('save')
            ->assertHasFormErrors(['currentPassword' => 'required']);

        Livewire::test(EditProfile::class)
            ->fillForm(['password' => self::NEW, 'passwordConfirmation' => self::NEW, 'currentPassword' => 'wrong-password-000'])
            ->call('save')
            ->assertHasFormErrors(['currentPassword']);

        $this->assertTrue(Hash::check(self::OLD, $this->editor->fresh()->password));

        Livewire::test(EditProfile::class)
            ->fillForm(['password' => self::NEW, 'passwordConfirmation' => self::NEW, 'currentPassword' => self::OLD])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check(self::NEW, $this->editor->fresh()->password));
    }

    public function test_changing_email_requires_the_current_password(): void
    {
        $this->actingAs($this->editor);

        Livewire::test(EditProfile::class)
            ->fillForm(['email' => 'new@example.test'])
            ->call('save')
            ->assertHasFormErrors(['currentPassword' => 'required']);

        Livewire::test(EditProfile::class)
            ->fillForm(['email' => 'new@example.test', 'currentPassword' => self::OLD])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('new@example.test', $this->editor->fresh()->email);
    }

    public function test_name_change_alone_needs_no_current_password(): void
    {
        $this->actingAs($this->editor);

        Livewire::test(EditProfile::class)
            ->fillForm(['name' => 'Renamed Editor'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Renamed Editor', $this->editor->fresh()->name);
    }

    public function test_weak_passwords_are_rejected_everywhere(): void
    {
        $this->actingAs($this->editor);
        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'short1', 'passwordConfirmation' => 'short1', 'currentPassword' => self::OLD])
            ->call('save')
            ->assertHasFormErrors(['password']);

        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'X', 'email' => 'x@example.test', 'password' => 'onlyletterspassword', 'roles' => [Role::findByName('editor')->id]])
            ->call('create')
            ->assertHasFormErrors(['password']);
    }
}
