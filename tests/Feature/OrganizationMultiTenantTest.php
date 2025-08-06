<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Facades\Tenancy;
use Tests\TestCase;

class OrganizationMultiTenantTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'is_super_admin' => true,
        ]);
        
        $this->superAdmin->assignRole('super_admin');
    }

    /** @test */
    public function it_can_create_organization_with_subdomain()
    {
        $organizationData = [
            'name' => 'Test Company',
            'slug' => 'test-company',
            'subdomain' => 'testcompany',
            'active' => true,
        ];

        $organization = Organization::create($organizationData);

        $this->assertDatabaseHas('tenants', [
            'id' => $organization->id,
            'data->name' => 'Test Company',
        ]);

        // Check if domain was created
        $this->assertDatabaseHas('domains', [
            'domain' => 'testcompany.test',
            'tenant_id' => $organization->id,
        ]);

        $this->organization = $organization;
    }

    /** @test */
    public function it_automatically_creates_tenant_database()
    {
        $organization = Organization::create([
            'name' => 'Database Test Company',
            'slug' => 'db-test-company',
            'subdomain' => 'dbtest',
            'active' => true,
        ]);

        // Initialize tenant context
        tenancy()->initialize($organization);

        // Check if tenant-specific tables exist
        $tables = collect(DB::select('SELECT name FROM sqlite_master WHERE type=\'table\' ORDER BY name'))
            ->pluck('name')->toArray();

        $expectedTables = [
            'assets',
            'checklist_templates',
            'checklist_questions',
            'inspections',
            'inspection_responses',
            'notifications',
            'notification_rules',
            'notification_logs',
            'users',
            'permissions',
            'roles',
        ];

        foreach ($expectedTables as $table) {
            $this->assertContains($table, $tables, "Table '{$table}' should exist in tenant database");
        }

        tenancy()->end();
    }

    /** @test */
    public function it_can_switch_tenant_context()
    {
        $org1 = Organization::create([
            'name' => 'Company One',
            'slug' => 'company-one',
            'subdomain' => 'compone',
            'active' => true,
        ]);

        $org2 = Organization::create([
            'name' => 'Company Two',
            'slug' => 'company-two', 
            'subdomain' => 'comptwo',
            'active' => true,
        ]);

        // Test tenant 1
        tenancy()->initialize($org1);
        $this->assertEquals($org1->id, tenant('id'));
        
        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@company1.com',
            'password' => bcrypt('password'),
        ]);
        
        $this->assertDatabaseHas('users', ['email' => 'user1@company1.com']);
        tenancy()->end();

        // Test tenant 2
        tenancy()->initialize($org2);
        $this->assertEquals($org2->id, tenant('id'));
        
        // User from tenant 1 should not exist in tenant 2
        $this->assertDatabaseMissing('users', ['email' => 'user1@company1.com']);
        
        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@company2.com',
            'password' => bcrypt('password'),
        ]);
        
        $this->assertDatabaseHas('users', ['email' => 'user2@company2.com']);
        tenancy()->end();

        // Verify isolation - switch back to tenant 1
        tenancy()->initialize($org1);
        $this->assertDatabaseHas('users', ['email' => 'user1@company1.com']);
        $this->assertDatabaseMissing('users', ['email' => 'user2@company2.com']);
        tenancy()->end();
    }

    /** @test */
    public function it_can_update_organization_subdomain()
    {
        $organization = Organization::create([
            'name' => 'Update Test Company',
            'slug' => 'update-test',
            'subdomain' => 'updatetest',
            'active' => true,
        ]);

        $originalDomain = Domain::where('tenant_id', $organization->id)->first();
        $this->assertEquals('updatetest.test', $originalDomain->domain);

        // Update subdomain
        $organization->update(['subdomain' => 'updatedtest']);

        // Check if new domain was created
        $newDomain = Domain::where('tenant_id', $organization->id)->first();
        $this->assertEquals('updatedtest.test', $newDomain->domain);

        // Original domain should be deleted
        $this->assertDatabaseMissing('domains', ['domain' => 'updatetest.test']);
    }

    /** @test */
    public function it_handles_organization_deletion_properly()
    {
        $organization = Organization::create([
            'name' => 'Delete Test Company',
            'slug' => 'delete-test',
            'subdomain' => 'deletetest',
            'active' => true,
        ]);

        $tenantId = $organization->id;
        $domain = Domain::where('tenant_id', $tenantId)->first();

        // Verify setup
        $this->assertNotNull($domain);
        $this->assertEquals('deletetest.test', $domain->domain);

        // Delete organization
        $organization->delete();

        // Verify cleanup
        $this->assertDatabaseMissing('tenants', ['id' => $tenantId]);
        $this->assertDatabaseMissing('domains', ['tenant_id' => $tenantId]);
    }

    /** @test */
    public function it_can_create_tenant_assets()
    {
        $organization = Organization::create([
            'name' => 'Asset Test Company',
            'slug' => 'asset-test',
            'subdomain' => 'assettest',
            'active' => true,
        ]);

        tenancy()->initialize($organization);

        // Create tenant user first
        $tenantUser = User::create([
            'name' => 'Tenant Admin',
            'email' => 'admin@assettest.com',
            'password' => bcrypt('password'),
        ]);

        // Create asset in tenant context
        $asset = \App\Models\Asset::create([
            'asset_id' => 'EQ-001',
            'name' => 'Test Equipment',
            'type' => 'Machinery',
            'make' => 'Test Make',
            'model' => 'Test Model',
            'serial_number' => 'SN-001',
            'active' => true,
            'qr_code' => 'test-qr-code',
        ]);

        $this->assertDatabaseHas('assets', [
            'asset_id' => 'EQ-001',
            'name' => 'Test Equipment',
        ]);

        tenancy()->end();

        // Asset should not exist in central database
        $this->assertDatabaseMissing('assets', [
            'asset_id' => 'EQ-001',
        ]);
    }

    /** @test */
    public function it_enforces_tenant_data_isolation()
    {
        $org1 = Organization::create([
            'name' => 'Isolation Test 1',
            'slug' => 'iso-test-1',
            'subdomain' => 'isotest1',
            'active' => true,
        ]);

        $org2 = Organization::create([
            'name' => 'Isolation Test 2',
            'slug' => 'iso-test-2',
            'subdomain' => 'isotest2',
            'active' => true,
        ]);

        // Create assets in tenant 1
        tenancy()->initialize($org1);
        $asset1 = \App\Models\Asset::create([
            'asset_id' => 'ORG1-001',
            'name' => 'Organization 1 Asset',
            'type' => 'Equipment',
            'active' => true,
            'qr_code' => 'org1-qr',
        ]);
        $tenant1AssetCount = \App\Models\Asset::count();
        tenancy()->end();

        // Create assets in tenant 2
        tenancy()->initialize($org2);
        $asset2 = \App\Models\Asset::create([
            'asset_id' => 'ORG2-001',
            'name' => 'Organization 2 Asset',
            'type' => 'Vehicle',
            'active' => true,
            'qr_code' => 'org2-qr',
        ]);
        $tenant2AssetCount = \App\Models\Asset::count();
        tenancy()->end();

        // Verify isolation
        $this->assertEquals(1, $tenant1AssetCount);
        $this->assertEquals(1, $tenant2AssetCount);

        // Verify tenant 1 can't see tenant 2's data
        tenancy()->initialize($org1);
        $this->assertDatabaseHas('assets', ['asset_id' => 'ORG1-001']);
        $this->assertDatabaseMissing('assets', ['asset_id' => 'ORG2-001']);
        tenancy()->end();

        // Verify tenant 2 can't see tenant 1's data
        tenancy()->initialize($org2);
        $this->assertDatabaseHas('assets', ['asset_id' => 'ORG2-001']);
        $this->assertDatabaseMissing('assets', ['asset_id' => 'ORG1-001']);
        tenancy()->end();
    }

    /** @test */
    public function it_handles_invalid_subdomain_gracefully()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Try to create organization with invalid subdomain (should fail unique constraint)
        Organization::create([
            'name' => 'Test Company 1',
            'subdomain' => 'duplicate',
            'active' => true,
        ]);

        Organization::create([
            'name' => 'Test Company 2',
            'subdomain' => 'duplicate', // This should fail
            'active' => true,
        ]);
    }

    /** @test */
    public function it_creates_default_settings_for_organization()
    {
        $organization = Organization::create([
            'name' => 'Settings Test Company',
            'slug' => 'settings-test',
            'subdomain' => 'settingstest',
            'active' => true,
        ]);

        $this->assertNotNull($organization->settings);
        $this->assertArrayHasKey('notifications', $organization->settings);
        $this->assertArrayHasKey('features', $organization->settings);
        $this->assertArrayHasKey('theme', $organization->settings);
        $this->assertArrayHasKey('limits', $organization->settings);

        // Test setting methods
        $organization->setSetting('test_key', 'test_value');
        $this->assertEquals('test_value', $organization->getSetting('test_key'));
        $this->assertEquals('default', $organization->getSetting('nonexistent', 'default'));
    }

    /** @test */
    public function it_can_filter_active_organizations()
    {
        Organization::create([
            'name' => 'Active Company',
            'subdomain' => 'active',
            'active' => true,
        ]);

        Organization::create([
            'name' => 'Inactive Company', 
            'subdomain' => 'inactive',
            'active' => false,
        ]);

        $activeOrgs = Organization::active()->get();
        $this->assertEquals(1, $activeOrgs->count());
        $this->assertEquals('Active Company', $activeOrgs->first()->name);
    }

    protected function tearDown(): void
    {
        // Clean up any tenant contexts
        if (tenancy()->initialized) {
            tenancy()->end();
        }
        
        parent::tearDown();
    }
}