<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create basic roles and permissions
        $this->setupBasicRolesAndPermissions();
    }

    private function setupBasicRolesAndPermissions(): void
    {
        // Create roles
        \Spatie\Permission\Models\Role::create(['name' => 'super_admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'manager']);
        \Spatie\Permission\Models\Role::create(['name' => 'operator']);

        // Create basic permissions
        $permissions = [
            'view_organizations',
            'create_organizations',
            'edit_organizations',
            'delete_organizations',
            'view_assets',
            'create_assets',
            'edit_assets',
            'delete_assets',
            'view_inspections',
            'create_inspections',
            'edit_inspections',
            'delete_inspections',
            'view_reports',
            'manage_users',
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }

        // Assign all permissions to super_admin
        $superAdminRole = \Spatie\Permission\Models\Role::findByName('super_admin');
        $superAdminRole->givePermissionTo($permissions);
    }
}