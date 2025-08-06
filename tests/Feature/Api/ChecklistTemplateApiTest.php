<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\ChecklistTemplate;
use Laravel\Sanctum\Sanctum;

class ChecklistTemplateApiTest extends TestCase
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

    public function test_can_list_templates()
    {
        $this->organization->run(function () {
            // Create test templates
            ChecklistTemplate::create([
                'name' => 'Vehicle Inspection',
                'description' => 'Daily vehicle inspection checklist',
                'asset_types' => ['vehicle'],
                'sections' => [
                    [
                        'name' => 'Exterior',
                        'questions' => [
                            [
                                'question' => 'Are the tires in good condition?',
                                'type' => 'radio',
                                'options' => ['Pass', 'Fail'],
                                'required' => true,
                            ]
                        ]
                    ]
                ],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            ChecklistTemplate::create([
                'name' => 'Equipment Check',
                'description' => 'Equipment safety checklist',
                'asset_types' => ['equipment'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/checklist-templates');

            $response->assertOk()
                ->assertJsonStructure([
                    'templates' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'description',
                                'asset_types',
                                'sections',
                                'active',
                            ]
                        ]
                    ]
                ]);

            $data = $response->json('templates.data');
            $this->assertCount(2, $data);
        });
    }

    public function test_can_filter_templates_by_asset_type()
    {
        $this->organization->run(function () {
            ChecklistTemplate::create([
                'name' => 'Vehicle Inspection',
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            ChecklistTemplate::create([
                'name' => 'Equipment Check',
                'asset_types' => ['equipment'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            ChecklistTemplate::create([
                'name' => 'Multi-type Check',
                'asset_types' => ['vehicle', 'equipment'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/checklist-templates?asset_type=vehicle');

            $response->assertOk();

            $data = $response->json('templates.data');
            $this->assertCount(2, $data); // Vehicle and Multi-type templates
        });
    }

    public function test_can_view_template()
    {
        $this->organization->run(function () {
            $template = ChecklistTemplate::create([
                'name' => 'Vehicle Inspection',
                'description' => 'Daily vehicle inspection checklist',
                'asset_types' => ['vehicle'],
                'sections' => [
                    [
                        'name' => 'Exterior',
                        'questions' => [
                            [
                                'question' => 'Are the tires in good condition?',
                                'type' => 'radio',
                                'options' => ['Pass', 'Fail'],
                                'required' => true,
                            ],
                            [
                                'question' => 'Check tire pressure',
                                'type' => 'number',
                                'unit' => 'PSI',
                                'required' => false,
                            ]
                        ]
                    ]
                ],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson("/api/checklist-templates/{$template->id}");

            $response->assertOk()
                ->assertJson([
                    'template' => [
                        'id' => $template->id,
                        'name' => 'Vehicle Inspection',
                        'description' => 'Daily vehicle inspection checklist',
                        'asset_types' => ['vehicle'],
                        'sections' => [
                            [
                                'name' => 'Exterior',
                                'questions' => [
                                    [
                                        'question' => 'Are the tires in good condition?',
                                        'type' => 'radio',
                                        'options' => ['Pass', 'Fail'],
                                        'required' => true,
                                    ],
                                    [
                                        'question' => 'Check tire pressure',
                                        'type' => 'number',
                                        'unit' => 'PSI',
                                        'required' => false,
                                    ]
                                ]
                            ]
                        ],
                        'active' => true,
                    ]
                ]);
        });
    }

    public function test_can_duplicate_template()
    {
        $this->organization->run(function () {
            $template = ChecklistTemplate::create([
                'name' => 'Original Template',
                'description' => 'Original description',
                'asset_types' => ['vehicle'],
                'sections' => [
                    [
                        'name' => 'Section 1',
                        'questions' => [
                            [
                                'question' => 'Test question?',
                                'type' => 'text',
                                'required' => true,
                            ]
                        ]
                    ]
                ],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->postJson("/api/checklist-templates/{$template->id}/duplicate", [
                'name' => 'Duplicated Template'
            ]);

            $response->assertCreated()
                ->assertJsonStructure([
                    'template' => [
                        'id',
                        'name',
                        'sections',
                    ]
                ]);

            $duplicatedTemplate = $response->json('template');
            $this->assertEquals('Duplicated Template', $duplicatedTemplate['name']);
            $this->assertEquals($template->sections, $duplicatedTemplate['sections']);
        });
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/checklist-templates');

        $response->assertUnauthorized();
    }

    public function test_template_not_found_returns_404()
    {
        $this->organization->run(function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/checklist-templates/999');

            $response->assertNotFound();
        });
    }

    public function test_cannot_access_other_organization_templates()
    {
        // Create another organization
        $otherOrg = Organization::create([
            'id' => 'other-org-456',
            'name' => 'Other Organization',
            'slug' => 'other-org',
            'active' => true,
        ]);

        $otherTemplateId = null;

        // Create template in the other organization
        $otherOrg->run(function () use (&$otherTemplateId) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            $otherUser = User::create([
                'name' => 'Other User',
                'email' => 'other@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            $otherTemplate = ChecklistTemplate::create([
                'name' => 'Other Template',
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
                'created_by' => $otherUser->id,
            ]);

            $otherTemplateId = $otherTemplate->id;
        });

        // Try to access from our organization
        $this->organization->run(function () use ($otherTemplateId) {
            Sanctum::actingAs($this->user);

            $response = $this->getJson("/api/checklist-templates/{$otherTemplateId}");

            $response->assertNotFound();
        });
    }
}
