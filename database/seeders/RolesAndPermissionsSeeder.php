<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'tickets.assign',
            'tickets.reply',
            'tickets.internal_note',
            'tickets.close',
            'tickets.reopen',
            'tickets.merge',
            'customers.view',
            'customers.update',
            'ai.view',
            'ai.use',
            'ai.configure',
            'sla.view',
            'sla.manage',
            'automation.view',
            'automation.manage',
            'reports.view',
            'settings.manage',
            'api.manage',
            'audit.view',
            'manage_users',
            'manage_settings',
            'manage_knowledge',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'tickets.assign',
            'tickets.reply',
            'tickets.internal_note',
            'tickets.close',
            'tickets.reopen',
            'tickets.merge',
            'customers.view',
            'customers.update',
            'ai.view',
            'ai.use',
            'sla.view',
            'sla.manage',
            'automation.view',
            'automation.manage',
            'reports.view',
            'audit.view',
            'manage_users',
            'manage_knowledge',
        ]);

        $agent = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        $agent->syncPermissions([
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.assign',
            'tickets.reply',
            'tickets.internal_note',
            'tickets.close',
            'tickets.reopen',
            'customers.view',
            'ai.view',
            'ai.use',
            'sla.view',
            'reports.view',
        ]);

        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }
}
