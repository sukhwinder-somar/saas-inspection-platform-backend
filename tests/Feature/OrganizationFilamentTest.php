<?php

namespace Tests\Feature;

use App\Filament\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationFilamentTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'is_super_admin' => true,
        ]);
        
        $this->actingAs($this->superAdmin);
    }

    /** @test */
    public function super_admin_can_access_organization_list()
    {
        $response = $this->get('/admin/organizations');
        $response->assertStatus(200);
    }

    /** @test */
    public function super_admin_can_create_organization()
    {
        $response = $this->get('/admin/organizations/create');
        $response->assertStatus(200);

        $organizationData = [
            'name' => 'Test Organization',
            'slug' => 'test-organization',
            'subdomain' => 'testorg',
            'active' => true,
        ];

        $response = $this->post('/admin/organizations', $organizationData);
        
        $this->assertDatabaseHas('tenants', [
            'data->name' => 'Test Organization',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'testorg.test',
        ]);
    }

    /** @test */
    public function organization_resource_displays_correct_columns()
    {
        $organization = Organization::create([
            'name' => 'Display Test Org',
            'slug' => 'display-test',
            'subdomain' => 'displaytest',
            'active' => true,
        ]);

        Livewire::test(OrganizationResource\Pages\ListOrganizations::class)
            ->assertCanSeeTableRecords([$organization])
            ->assertTableColumnExists('name')
            ->assertTableColumnExists('slug')
            ->assertTableColumnExists('subdomain')
            ->assertTableColumnExists('active')
            ->assertTableColumnExists('users_count')
            ->assertTableColumnExists('trial_ends_at')
            ->assertTableColumnExists('created_at');
    }

    /** @test */
    public function organization_resource_can_filter_by_active_status()
    {
        $activeOrg = Organization::create([
            'name' => 'Active Organization',
            'subdomain' => 'activeorg',
            'active' => true,
        ]);

        $inactiveOrg = Organization::create([
            'name' => 'Inactive Organization',
            'subdomain' => 'inactiveorg',
            'active' => false,
        ]);

        Livewire::test(OrganizationResource\Pages\ListOrganizations::class)
            ->assertCanSeeTableRecords([$activeOrg, $inactiveOrg])
            ->filterTable('active', true)
            ->assertCanSeeTableRecords([$activeOrg])
            ->assertCanNotSeeTableRecords([$inactiveOrg]);
    }

    /** @test */
    public function organization_form_validates_required_fields()
    {
        Livewire::test(OrganizationResource\Pages\CreateOrganization::class)
            ->fillForm([
                'slug' => '', // Required field left empty
                'subdomain' => 'testorg',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'slug']);
    }

    /** @test */
    public function organization_form_validates_unique_fields()
    {
        $existingOrg = Organization::create([
            'name' => 'Existing Organization',
            'slug' => 'existing-org',
            'subdomain' => 'existingorg',
            'active' => true,
        ]);

        Livewire::test(OrganizationResource\Pages\CreateOrganization::class)
            ->fillForm([
                'name' => 'New Organization',
                'slug' => 'existing-org', // Should fail unique validation
                'subdomain' => 'existingorg', // Should fail unique validation
            ])
            ->call('create')
            ->assertHasFormErrors(['slug', 'subdomain']);
    }

    /** @test */
    public function organization_slug_auto_generates_from_name()
    {
        Livewire::test(OrganizationResource\Pages\CreateOrganization::class)
            ->fillForm([
                'name' => 'Auto Slug Test Company',
            ])
            ->assertFormSet([
                'slug' => 'auto-slug-test-company',
            ]);
    }

    /** @test */
    public function organization_form_accepts_valid_data()
    {
        $formData = [
            'name' => 'Valid Organization',
            'slug' => 'valid-organization',
            'subdomain' => 'validorg',
            'active' => true,
            'stripe_customer_id' => 'cus_test123',
            'data' => [
                'industry' => 'Manufacturing',
            ],
            'settings' => [
                'timezone' => 'UTC',
            ],
        ];

        Livewire::test(OrganizationResource\Pages\CreateOrganization::class)
            ->fillForm($formData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tenants', [
            'data->name' => 'Valid Organization',
            'data->stripe_customer_id' => 'cus_test123',
        ]);
    }

    /** @test */
    public function super_admin_can_edit_organization()
    {
        $organization = Organization::create([
            'name' => 'Original Name',
            'slug' => 'original-name',
            'subdomain' => 'original',
            'active' => true,
        ]);

        Livewire::test(OrganizationResource\Pages\EditOrganization::class, [
            'record' => $organization->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'slug' => 'updated-name',
                'active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $organization->refresh();
        $this->assertEquals('Updated Name', $organization->name);
        $this->assertEquals('updated-name', $organization->slug);
        $this->assertFalse($organization->active);
    }

    /** @test */
    public function super_admin_can_view_organization_details()
    {
        $organization = Organization::create([
            'name' => 'View Test Organization',
            'slug' => 'view-test',
            'subdomain' => 'viewtest',
            'active' => true,
            'trial_ends_at' => now()->addDays(30),
        ]);

        Livewire::test(OrganizationResource\Pages\ViewOrganization::class, [
            'record' => $organization->getRouteKey(),
        ])
            ->assertSeeText('View Test Organization')
            ->assertSeeText('view-test')
            ->assertSeeText('viewtest');
    }

    /** @test */
    public function access_tenant_action_is_visible_for_organizations_with_subdomain()
    {
        $orgWithSubdomain = Organization::create([
            'name' => 'Org with Subdomain',
            'subdomain' => 'withsub',
            'active' => true,
        ]);

        $orgWithoutSubdomain = Organization::create([
            'name' => 'Org without Subdomain',
            'active' => true,
        ]);

        Livewire::test(OrganizationResource\Pages\ListOrganizations::class)
            ->assertTableActionVisible('access_tenant', $orgWithSubdomain)
            ->assertTableActionHidden('access_tenant', $orgWithoutSubdomain);
    }

    /** @test */
    public function access_tenant_action_generates_correct_url()
    {
        $organization = Organization::create([
            'name' => 'URL Test Org',
            'subdomain' => 'urltest',
            'active' => true,
        ]);

        $resource = new OrganizationResource();
        $table = $resource::table($resource->getTable());
        
        $actions = $table->getActions();
        $accessAction = collect($actions)->first(fn($action) => $action->getName() === 'access_tenant');
        
        $this->assertNotNull($accessAction);
        
        $url = $accessAction->getUrl($organization);
        $this->assertEquals('https://urltest.test/app', $url);
    }

    /** @test */
    public function organization_resource_shows_users_count()
    {
        $organization = Organization::create([
            'name' => 'User Count Test',
            'subdomain' => 'usercount',
            'active' => true,
        ]);

        // Initialize tenant and create users
        tenancy()->initialize($organization);
        
        User::create([
            'name' => 'Tenant User 1',
            'email' => 'user1@tenant.com',
            'password' => bcrypt('password'),
        ]);
        
        User::create([
            'name' => 'Tenant User 2',
            'email' => 'user2@tenant.com',
            'password' => bcrypt('password'),
        ]);
        
        tenancy()->end();

        // Load the organization with user count
        $organization = $organization->fresh();
        $organization->loadCount('users');

        $this->assertEquals(2, $organization->users_count);
    }

    /** @test */
    public function organization_resource_handles_search()
    {
        $org1 = Organization::create([
            'name' => 'Search Test Alpha',
            'slug' => 'search-alpha',
            'subdomain' => 'searchalpha',
            'active' => true,
        ]);

        $org2 = Organization::create([
            'name' => 'Search Test Beta',
            'slug' => 'search-beta', 
            'subdomain' => 'searchbeta',
            'active' => true,
        ]);

        Livewire::test(OrganizationResource\Pages\ListOrganizations::class)
            ->assertCanSeeTableRecords([$org1, $org2])
            ->searchTable('Alpha')
            ->assertCanSeeTableRecords([$org1])
            ->assertCanNotSeeTableRecords([$org2])
            ->searchTable('search-beta')
            ->assertCanSeeTableRecords([$org2])
            ->assertCanNotSeeTableRecords([$org1]);
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