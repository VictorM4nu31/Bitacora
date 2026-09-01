<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view customers', 'create customers', 'update customers', 'delete customers',
            'view equipment', 'create equipment', 'update equipment', 'delete equipment',
            'view services', 'create services', 'update services', 'delete services',
            'view reports', 'update reports', 'finalize reports', 'generate pdf', 'share reports',
            'manage maintenance',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $technician = Role::firstOrCreate(['name' => 'technician']);
        $technician->syncPermissions([
            'view customers', 'create customers', 'update customers',
            'view equipment', 'create equipment', 'update equipment',
            'view services', 'create services', 'update services',
            'view reports', 'update reports', 'finalize reports', 'generate pdf', 'share reports',
        ]);
    }
}
