<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Local/dev convenience data only — production installs create their
        // admin account interactively via the /install wizard instead.
        if (! app()->environment('local')) {
            return;
        }

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $superAdmin = User::updateOrCreate(
            ['email' => 'm.m.h.89@hotmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('admin'),
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->syncRoles([$superAdminRole]);
    }
}
