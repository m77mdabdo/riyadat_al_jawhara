<?php

namespace App\Models;

use App\Models\Concerns\LogsAdminActivity;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsAdminActivity, Notifiable;

    /** Never log password hashes or tokens. */
    /** @var list<string> */
    protected array $activityLogOnly = ['name', 'email', 'is_active'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'is_active',
        'password_change_required',
    ];

    /** Column defaults, so a new user behaves like the migration. */
    protected $attributes = [
        'is_active' => true,
        'password_change_required' => false,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'password_change_required' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Active accounts with at least one role (the built-in admin/editor or a
     * role created in Administration → Roles). Checked on every request, so
     * deactivating an account locks it out immediately.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // A missing value (code deployed before the migration) counts as active.
        return ($this->is_active ?? true) !== false && $this->roles->isNotEmpty();
    }

    protected static function booted(): void
    {
        // A password the user sets clears the "new password required" flag.
        static::saving(function (self $user) {
            if ($user->exists && $user->isDirty('password') && ! $user->isDirty('password_change_required')) {
                $user->password_change_required = false;
            }
        });
    }

    /**
     * Whether access is decided by role permissions (Administration → Roles)
     * rather than the built-in admin/editor rules: true once the permissions
     * exist (app:import-site-content). From then on a role without a
     * permission really can't do that thing.
     */
    public function usesPermissions(): bool
    {
        return app(PermissionRegistrar::class)->getPermissions()->isNotEmpty();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->getAllPermissions()->contains('name', $permission);
    }

    /** Emails and bell notifications use the user's admin language. */
    public function preferredLocale(): string
    {
        return in_array($this->locale, config('locales.supported'), true) ? $this->locale : config('app.locale');
    }
}
