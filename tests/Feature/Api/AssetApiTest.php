<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\Asset;
use Laravel\Sanctum\Sanctum;

class AssetApiTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organization (tenant)
        $this->organization = Organization::create([
            'id' => 'test-org-123',
            'name' => 'Test Organization',
            'slug' => 'test-org',
            'active' => true,
        ]);

        // Run migrations for the tenant
        $this->organization->run(function () {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            // Create a user within the tenant context
            $this->user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        });
    }

    public function test_can_list_assets()
    {
        $this->organization->run(function () {
            // Create test assets
            $asset1 = Asset::create([
                'name' => 'Test Vehicle 1',
                'asset_id' => 'VEH-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-VEH001',
            ]);

            $asset2 = Asset::create([
                'name' => 'Test Machinery 1',
                'asset_id' => 'MACH-001',
                'type' => 'machinery',
                'active' => true,
                'qr_code' => 'QR-MACH001',
            ]);

            // Authenticate user
            Sanctum::actingAs($this->user);

            // Make API request
            $response = $this->getJson('/api/assets');

            $response->assertOk()
                ->assertJsonStructure([
                    'assets' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'asset_id',
                                'type',
                                'qr_code',
                                'active'
                            ]
                        ]
                    ]
                ]);

            $data = $response->json('assets.data');
            $this->assertCount(2, $data);
        });
    }

    public function test_can_view_asset_by_qr_code()
    {
        $this->organization->run(function () {
            $asset = Asset::create([
                'name' => 'Test Vehicle',
                'asset_id' => 'VEH-001',
                'type' => 'vehicle',
                'make' => 'Toyota',
                'model' => 'Camry',
                'active' => true,
                'qr_code' => 'QR-VEH001',
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/assets/QR-VEH001');

            $response->assertOk()
                ->assertJson([
                    'asset' => [
                        'id' => $asset->id,
                        'name' => 'Test Vehicle',
                        'asset_id' => 'VEH-001',
                        'type' => 'vehicle',
                        'make' => 'Toyota',
                        'model' => 'Camry',
                        'qr_code' => 'QR-VEH001',
                    ]
                ]);
        });
    }

    public function test_returns_404_for_nonexistent_asset()
    {
        $this->organization->run(function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/assets/NONEXISTENT-QR');

            $response->assertNotFound()
                ->assertJson([
                    'message' => 'Asset not found'
                ]);
        });
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/assets');

        $response->assertUnauthorized();
    }

    public function test_asset_organization_isolation()
    {
        // Create another organization
        $otherOrg = Organization::create([
            'id' => 'other-org-456',
            'name' => 'Other Organization',
            'slug' => 'other-org',
            'active' => true,
        ]);

        // Create asset in the other organization
        $otherOrg->run(function () {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            Asset::create([
                'name' => 'Other Org Asset',
                'asset_id' => 'OTHER-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-OTHER001',
            ]);
        });

        // Try to access from our organization
        $this->organization->run(function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/assets/QR-OTHER001');

            $response->assertNotFound();
        });
    }
}
