<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@jra.sa'],
            ['name' => 'JRA Admin', 'password' => bcrypt('password')]
        );
        $adminUser->assignRole($admin);

        $editorUser = User::firstOrCreate(
            ['email' => 'editor@jra.sa'],
            ['name' => 'JRA Editor', 'password' => bcrypt('password')]
        );
        $editorUser->assignRole('editor');
    }
}
