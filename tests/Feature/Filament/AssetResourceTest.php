<?php

namespace Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\Asset;
use App\Filament\Resources\AssetResource;
use Livewire\Livewire;
use Filament\Actions\DeleteAction;

class AssetResourceTest extends TestCase
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

            // Create test data within the tenant context
            $this->user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        });
    }

    public function test_can_render_asset_list_page()
    {
        $this->organization->run(function () {
            $this->actingAs($this->user);

            Livewire::test(AssetResource\Pages\ListAssets::class)
                ->assertSuccessful();
        });
    }

    public function test_can_list_assets()
    {
        $this->organization->run(function () {
            // Create test assets
            Asset::create([
                'name' => 'Test Vehicle 1',
                'asset_id' => 'VEH-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-VEH001',
            ]);

            Asset::create([
                'name' => 'Test Equipment 1',
                'asset_id' => 'EQP-001',
                'type' => 'equipment',
                'active' => true,
                'qr_code' => 'QR-EQP001',
            ]);

            $this->actingAs($this->user);

            Livewire::test(AssetResource\Pages\ListAssets::class)
                ->assertCanSeeTableRecords([
                    Asset::where('asset_id', 'VEH-001')->first(),
                    Asset::where('asset_id', 'EQP-001')->first(),
                ]);
        });
    }

    public function test_can_create_asset()
    {
        $this->organization->run(function () {
            $this->actingAs($this->user);

            $newData = [
                'name' => 'New Test Vehicle',
                'asset_id' => 'VEH-NEW-001',
                'type' => 'vehicle',
                'description' => 'A test vehicle for unit testing',
                'location' => 'Test Location',
                'active' => true,
            ];

            Livewire::test(AssetResource\Pages\CreateAsset::class)
                ->fillForm($newData)
                ->call('create')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas('assets', [
                'name' => 'New Test Vehicle',
                'asset_id' => 'VEH-NEW-001',
                'type' => 'vehicle',
                'active' => true,
            ]);
        });
    }

    public function test_can_edit_asset()
    {
        $this->organization->run(function () {
            $asset = Asset::create([
                'name' => 'Original Name',
                'asset_id' => 'VEH-EDIT-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-EDIT001',
            ]);

            $this->actingAs($this->user);

            $newData = [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'location' => 'Updated Location',
            ];

            Livewire::test(AssetResource\Pages\EditAsset::class, [
                'record' => $asset->getRouteKey(),
            ])
                ->fillForm($newData)
                ->call('save')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas('assets', [
                'id' => $asset->id,
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'location' => 'Updated Location',
            ]);
        });
    }

    public function test_can_delete_asset()
    {
        $this->organization->run(function () {
            $asset = Asset::create([
                'name' => 'Asset to Delete',
                'asset_id' => 'VEH-DELETE-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-DELETE001',
            ]);

            $this->actingAs($this->user);

            Livewire::test(AssetResource\Pages\EditAsset::class, [
                'record' => $asset->getRouteKey(),
            ])
                ->callAction(DeleteAction::class);

            $this->assertModelMissing($asset);
        });
    }

    public function test_asset_id_must_be_unique()
    {
        $this->organization->run(function () {
            Asset::create([
                'name' => 'Existing Asset',
                'asset_id' => 'DUPLICATE-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-DUP001',
            ]);

            $this->actingAs($this->user);

            $duplicateData = [
                'name' => 'New Asset',
                'asset_id' => 'DUPLICATE-001', // Same as existing
                'type' => 'equipment',
                'active' => true,
            ];

            Livewire::test(AssetResource\Pages\CreateAsset::class)
                ->fillForm($duplicateData)
                ->call('create')
                ->assertHasFormErrors(['asset_id']);
        });
    }

    public function test_can_filter_assets_by_type()
    {
        $this->organization->run(function () {
            Asset::create([
                'name' => 'Test Vehicle',
                'asset_id' => 'VEH-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-VEH001',
            ]);

            Asset::create([
                'name' => 'Test Equipment',
                'asset_id' => 'EQP-001',
                'type' => 'equipment',
                'active' => true,
                'qr_code' => 'QR-EQP001',
            ]);

            $this->actingAs($this->user);

            Livewire::test(AssetResource\Pages\ListAssets::class)
                ->filterTable('type', 'vehicle')
                ->assertCanSeeTableRecords([
                    Asset::where('type', 'vehicle')->first(),
                ])
                ->assertCanNotSeeTableRecords([
                    Asset::where('type', 'equipment')->first(),
                ]);
        });
    }

    public function test_can_search_assets()
    {
        $this->organization->run(function () {
            Asset::create([
                'name' => 'Search Test Vehicle',
                'asset_id' => 'VEH-SEARCH-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-SEARCH001',
            ]);

            Asset::create([
                'name' => 'Other Asset',
                'asset_id' => 'OTHER-001',
                'type' => 'equipment',
                'active' => true,
                'qr_code' => 'QR-OTHER001',
            ]);

            $this->actingAs($this->user);

            Livewire::test(AssetResource\Pages\ListAssets::class)
                ->searchTable('Search Test')
                ->assertCanSeeTableRecords([
                    Asset::where('name', 'Search Test Vehicle')->first(),
                ])
                ->assertCanNotSeeTableRecords([
                    Asset::where('name', 'Other Asset')->first(),
                ]);
        });
    }

    public function test_qr_code_is_generated_automatically()
    {
        $this->organization->run(function () {
            $this->actingAs($this->user);

            $newData = [
                'name' => 'QR Test Asset',
                'asset_id' => 'QR-TEST-001',
                'type' => 'vehicle',
                'active' => true,
            ];

            Livewire::test(AssetResource\Pages\CreateAsset::class)
                ->fillForm($newData)
                ->call('create')
                ->assertHasNoFormErrors();

            $asset = Asset::where('asset_id', 'QR-TEST-001')->first();
            $this->assertNotNull($asset->qr_code);
            $this->assertStringContainsString('QR-', $asset->qr_code);
        });
    }
}
