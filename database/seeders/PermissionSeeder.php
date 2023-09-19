<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // user
        Permission::create(['name' => 'add-user', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'delete-user', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'edit-user', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'manage-user-status', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'view-user', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'view-users', 'guard_name' => 'sanctum']);

        // Settings
        Permission::create(['name' => 'add-setting', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'delete-setting', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'edit-setting', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'manage-setting-status', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'view-setting', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'view-settings', 'guard_name' => 'sanctum']);

        // Permission
        Permission::create(['name' => 'add-permission', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'delete-permission', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'edit-permission', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'manage-permission-status', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'view-permission', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'view-permissions', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'give-permission', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'revoke-permission', 'guard_name' => 'sanctum']);

        // Role
        Permission::create(['name' => 'add-role', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'delete-role', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'edit-role', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'manage-role-status', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'view-role', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'view-roles', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'assign-role', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'remove-role', 'guard_name' => 'sanctum']);
    }
}
