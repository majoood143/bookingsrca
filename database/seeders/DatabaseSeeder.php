<?php

namespace Database\Seeders;

use App\Models\PosTerminal;
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

        $this->call(PosRolesSeeder::class);

        PosTerminal::firstOrCreate(
            ['name' => 'Gate 1'],
            ['location' => 'Main Entrance', 'is_active' => true],
        );

        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Demo Cashier',
                'password' => bcrypt('cashier'),
                'email_verified_at' => now(),
            ]
        );

        $cashier->syncRoles([$cashierRole]);
    }
}
