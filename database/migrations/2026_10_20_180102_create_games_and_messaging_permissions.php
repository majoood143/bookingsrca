<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The Shield permissions for the Games module, the message log and the WhatsApp plugin, given
 * to super_admin, on installs that already ran shield:generate. Shield's super admin is a role
 * with explicit permissions here (not a gate bypass), so those installs would otherwise not see
 * these screens.
 */
return new class extends Migration
{
    private const MODELS = ['GameCampaign', 'GamePlayer', 'GameAttempt', 'MessageLog', 'WhatsappInstance', 'WhatsappMessage', 'WhatsappWebhook'];

    private const ACTIONS = ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny', 'Restore', 'ForceDelete', 'ForceDeleteAny', 'RestoreAny', 'Replicate', 'Reorder'];

    public function up(): void
    {
        // A fresh install has no permissions yet: the installer runs shield:generate after migrating
        // (which creates these too) and reads an empty table as "not set up". Leave it empty.
        if (! Schema::hasTable('permissions') || Permission::query()->count() === 0) {
            return;
        }

        $names = $this->names();

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        Role::query()->where('name', 'super_admin')->where('guard_name', 'web')->first()?->givePermissionTo($names);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        Permission::query()->whereIn('name', $this->names())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return list<string> */
    private function names(): array
    {
        $names = [];

        foreach (self::MODELS as $model) {
            foreach (self::ACTIONS as $action) {
                $names[] = "{$action}:{$model}";
            }
        }

        return $names;
    }
};
