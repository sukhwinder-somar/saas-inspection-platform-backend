<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Asset permissions
            'view_assets',
            'create_assets',
            'edit_assets',
            'delete_assets',
            'assign_assets',

            // Checklist permissions
            'view_checklists',
            'create_checklists',
            'edit_checklists',
            'delete_checklists',
            'publish_checklists',

            // Inspection permissions
            'view_inspections',
            'create_inspections',
            'edit_inspections',
            'delete_inspections',
            'complete_inspections',

            // User management permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_roles',

            // Organization permissions
            'view_organization',
            'edit_organization',
            'manage_subscription',
            'view_billing',

            // Report permissions
            'view_reports',
            'export_reports',
            'view_analytics',

            // Notification permissions
            'view_notifications',
            'manage_notifications',
            'send_notifications',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin (for SaaS platform management)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Organization Admin (tenant admin)
        $orgAdmin = Role::firstOrCreate(['name' => 'admin']);
        $orgAdmin->givePermissionTo([
            'view_assets', 'create_assets', 'edit_assets', 'delete_assets', 'assign_assets',
            'view_checklists', 'create_checklists', 'edit_checklists', 'delete_checklists', 'publish_checklists',
            'view_inspections', 'create_inspections', 'edit_inspections', 'delete_inspections',
            'view_users', 'create_users', 'edit_users', 'delete_users', 'manage_roles',
            'view_organization', 'edit_organization', 'manage_subscription', 'view_billing',
            'view_reports', 'export_reports', 'view_analytics',
            'view_notifications', 'manage_notifications', 'send_notifications',
        ]);

        // Manager (can view reports and manage some operations)
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'view_assets', 'assign_assets',
            'view_checklists',
            'view_inspections', 'create_inspections', 'edit_inspections',
            'view_users',
            'view_reports', 'export_reports', 'view_analytics',
            'view_notifications', 'send_notifications',
        ]);

        // Operator (field workers who perform inspections)
        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->givePermissionTo([
            'view_assets',
            'view_checklists',
            'view_inspections', 'create_inspections', 'complete_inspections',
            'view_notifications',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles: super_admin, admin, manager, operator');
        $this->command->info('Created ' . count($permissions) . ' permissions');
    }
}
