<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Creates the two POS-specific roles from §7 of the POS spec. super_admin
 * already exists and bypasses all permission checks (Shield's Gate::before),
 * so it isn't recreated here.
 *
 * - cashier: the POS screen only (shift open/close, sell, void own same-shift
 *   sales) — that screen is a custom page gated by role directly, not by
 *   Shield resource permissions, so this role intentionally gets none of the
 *   generated Pos* CRUD permissions.
 * - pos_manager: cashier's abilities plus refund authorization, discount
 *   override approval, and visibility into every terminal's shift history —
 *   modeled here as read access across POS resources, the reconciliation
 *   dashboard, and the ability to manage products/discounts.
 */
class PosRolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        $posManager = Role::firstOrCreate(['name' => 'pos_manager', 'guard_name' => 'web']);

        $posManagerPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%:PosTransaction')
                        ->orWhere('name', 'like', '%:PosShift')
                        ->orWhere('name', 'like', '%:PosTerminal')
                        ->orWhere('name', 'like', '%:PosReconciliation');
                })->orWhere(function ($q) {
                    // Products/discounts: everything except the destructive
                    // delete/force-delete/restore actions.
                    $q->where(function ($qq) {
                        $qq->where('name', 'like', '%:PosProduct')
                            ->orWhere('name', 'like', '%:PosDiscount');
                    })->where(function ($qq) {
                        $qq->where('name', 'like', 'ViewAny:%')
                            ->orWhere('name', 'like', 'View:%')
                            ->orWhere('name', 'like', 'Create:%')
                            ->orWhere('name', 'like', 'Update:%');
                    });
                });
            })
            ->pluck('name')
            ->all();

        $posManager->syncPermissions($posManagerPermissions);
    }
}
