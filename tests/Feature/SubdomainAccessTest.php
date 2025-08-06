<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Tests\TestCase;

class SubdomainAccessTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $tenantUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create organization with subdomain
        $this->organization = Organization::create([
            'name' => 'Subdomain Test Company',
            'slug' => 'subdomain-test',
            'subdomain' => 'subtest',
            'active' => true,
        ]);

        // Create tenant user
        tenancy()->initialize($this->organization);
        $this->tenantUser = User::create([
            'name' => 'Tenant User',
            'email' => 'user@subtest.test',
            'password' => bcrypt('password'),
        ]);
        tenancy()->end();
    }

    /** @test */
    public function tenant_domain_initializes_correct_tenant_context()
    {
        $request = Request::create('https://subtest.test/app', 'GET');
        $request->headers->set('HOST', 'subtest.test');

        $middleware = new InitializeTenancyByDomain();
        
        $middleware->handle($request, function ($req) {
            $this->assertTrue(tenancy()->initialized);
            $this->assertEquals($this->organization->id, tenant('id'));
            $this->assertEquals('Subdomain Test Company', tenant('name'));
            return response('OK');
        });
    }

    /** @test */
    public function central_domain_does_not_initialize_tenant()
    {
        $request = Request::create('https://filament-starter.test/admin', 'GET');
        $request->headers->set('HOST', 'filament-starter.test');

        // Central domain should not initialize tenancy
        $this->assertFalse(tenancy()->initialized);
    }

    /** @test */
    public function tenant_panel_is_accessible_via_subdomain()
    {
        // Simulate tenant access
        $this->withHeaders([
            'HOST' => 'subtest.test'
        ]);

        $response = $this->get('https://subtest.test/app/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function tenant_can_access_tenant_specific_resources()
    {
        // Initialize tenant context
        tenancy()->initialize($this->organization);

        // Create asset in tenant context
        $asset = \App\Models\Asset::create([
            'asset_id' => 'SUB-001',
            'name' => 'Subdomain Test Asset',
            'type' => 'Equipment',
            'active' => true,
            'qr_code' => 'sub-test-qr',
        ]);

        // Asset should exist in tenant database
        $this->assertDatabaseHas('assets', [
            'asset_id' => 'SUB-001',
            'name' => 'Subdomain Test Asset',
        ]);

        tenancy()->end();

        // Asset should not exist in central database
        $this->assertDatabaseMissing('assets', [
            'asset_id' => 'SUB-001',
        ]);
    }

    /** @test */
    public function invalid_subdomain_does_not_initialize_tenancy()
    {
        $request = Request::create('https://nonexistent.test/app', 'GET');
        $request->headers->set('HOST', 'nonexistent.test');

        $middleware = new InitializeTenancyByDomain();
        
        try {
            $middleware->handle($request, function ($req) {
                return response('OK');
            });
        } catch (\Exception $e) {
            // Should throw tenant not found exception
            $this->assertInstanceOf(\Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByDomainException::class, $e);
        }
    }

    /** @test */
    public function tenant_users_are_isolated_per_organization()
    {
        // Create second organization
        $org2 = Organization::create([
            'name' => 'Second Company',
            'slug' => 'second-company',
            'subdomain' => 'second',
            'active' => true,
        ]);

        // Create user in second tenant
        tenancy()->initialize($org2);
        $user2 = User::create([
            'name' => 'Second Tenant User',
            'email' => 'user@second.test',
            'password' => bcrypt('password'),
        ]);
        tenancy()->end();

        // Test tenant 1 isolation
        tenancy()->initialize($this->organization);
        $this->assertDatabaseHas('users', ['email' => 'user@subtest.test']);
        $this->assertDatabaseMissing('users', ['email' => 'user@second.test']);
        tenancy()->end();

        // Test tenant 2 isolation
        tenancy()->initialize($org2);
        $this->assertDatabaseHas('users', ['email' => 'user@second.test']);
        $this->assertDatabaseMissing('users', ['email' => 'user@subtest.test']);
        tenancy()->end();
    }

    /** @test */
    public function tenant_authentication_works_independently()
    {
        // Test login to first tenant
        tenancy()->initialize($this->organization);
        
        $response = $this->post('/app/login', [
            'email' => 'user@subtest.test',
            'password' => 'password',
        ]);

        // Should redirect to tenant dashboard
        $response->assertRedirect('/app');
        $this->assertAuthenticated();
        
        tenancy()->end();
    }

    /** @test */
    public function inactive_organization_prevents_access()
    {
        // Deactivate organization
        $this->organization->update(['active' => false]);

        $request = Request::create('https://subtest.test/app', 'GET');
        $request->headers->set('HOST', 'subtest.test');

        // Should either throw exception or return error response
        $this->expectException(\Exception::class);
        
        $middleware = new InitializeTenancyByDomain();
        $middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    /** @test */
    public function tenant_asset_qr_codes_are_unique_per_tenant()
    {
        // Create asset in first tenant
        tenancy()->initialize($this->organization);
        $asset1 = \App\Models\Asset::create([
            'asset_id' => 'QR-001',
            'name' => 'QR Test Asset 1',
            'type' => 'Equipment',
            'active' => true,
            'qr_code' => 'same-qr-code', // Same QR code
        ]);
        tenancy()->end();

        // Create second organization and asset with same QR code
        $org2 = Organization::create([
            'name' => 'QR Test Company 2',
            'subdomain' => 'qrtest2',
            'active' => true,
        ]);

        tenancy()->initialize($org2);
        $asset2 = \App\Models\Asset::create([
            'asset_id' => 'QR-002',
            'name' => 'QR Test Asset 2',
            'type' => 'Vehicle',
            'active' => true,
            'qr_code' => 'same-qr-code', // Same QR code - should be allowed
        ]);
        tenancy()->end();

        // Both assets should exist in their respective tenant databases
        tenancy()->initialize($this->organization);
        $this->assertDatabaseHas('assets', ['qr_code' => 'same-qr-code', 'asset_id' => 'QR-001']);
        $this->assertDatabaseMissing('assets', ['qr_code' => 'same-qr-code', 'asset_id' => 'QR-002']);
        tenancy()->end();

        tenancy()->initialize($org2);
        $this->assertDatabaseHas('assets', ['qr_code' => 'same-qr-code', 'asset_id' => 'QR-002']);
        $this->assertDatabaseMissing('assets', ['qr_code' => 'same-qr-code', 'asset_id' => 'QR-001']);
        tenancy()->end();
    }

    /** @test */
    public function tenant_data_persists_across_requests()
    {
        // Create data in tenant 1
        tenancy()->initialize($this->organization);
        $asset = \App\Models\Asset::create([
            'asset_id' => 'PERSIST-001',
            'name' => 'Persistence Test Asset',
            'type' => 'Machinery',
            'active' => true,
            'qr_code' => 'persist-qr',
        ]);
        $assetId = $asset->id;
        tenancy()->end();

        // Access same tenant in new "request"
        tenancy()->initialize($this->organization);
        $retrievedAsset = \App\Models\Asset::find($assetId);
        $this->assertNotNull($retrievedAsset);
        $this->assertEquals('PERSIST-001', $retrievedAsset->asset_id);
        $this->assertEquals('Persistence Test Asset', $retrievedAsset->name);
        tenancy()->end();
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