<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The supported way to create the first (or any) admin account.
 *
 * The password is always prompted with hidden input -- there is deliberately
 * no --password option, so it never lands in shell history or process lists.
 */
class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin
                            {--name= : Full name (prompted if omitted)}
                            {--email= : Login email (prompted if omitted)}';

    protected $description = 'Create an admin user for the /admin panel (prompts for a hidden password)';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->secret('Password (min. 12 characters, letters and numbers)');
        $confirmation = $this->secret('Confirm password');

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $confirmation,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->assignRole($role);

        $this->info("Admin user {$user->email} created. Sign in at ".url('/admin'));

        return self::SUCCESS;
    }
}
