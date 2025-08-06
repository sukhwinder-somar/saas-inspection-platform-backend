<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\Asset;
use App\Models\Inspection;
use App\Models\ChecklistTemplate;
use Laravel\Sanctum\Sanctum;

class InspectionApiTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;
    protected $user;
    protected $asset;
    protected $template;

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

            $this->asset = Asset::create([
                'name' => 'Test Vehicle',
                'asset_id' => 'VEH-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-VEH001',
            ]);

            $this->template = ChecklistTemplate::create([
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
                                'question' => 'Are the lights working?',
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
        });
    }

    public function test_can_list_inspections()
    {
        $this->organization->run(function () {
            // Create test inspections
            $inspection1 = Inspection::create([
                'asset_id' => $this->asset->id,
                'template_id' => $this->template->id,
                'inspector_id' => $this->user->id,
                'scheduled_at' => now()->addHour(),
                'status' => 'pending',
            ]);

            $inspection2 = Inspection::create([
                'asset_id' => $this->asset->id,
                'template_id' => $this->template->id,
                'inspector_id' => $this->user->id,
                'scheduled_at' => now()->addDays(1),
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/inspections');

            $response->assertOk()
                ->assertJsonStructure([
                    'inspections' => [
                        'data' => [
                            '*' => [
                                'id',
                                'asset',
                                'template',
                                'status',
                                'scheduled_at',
                            ]
                        ]
                    ]
                ]);

            $data = $response->json('inspections.data');
            $this->assertCount(2, $data);
        });
    }

    public function test_can_view_inspection()
    {
        $this->organization->run(function () {
            $inspection = Inspection::create([
                'asset_id' => $this->asset->id,
                'template_id' => $this->template->id,
                'inspector_id' => $this->user->id,
                'scheduled_at' => now()->addHour(),
                'status' => 'pending',
                'notes' => 'Test inspection notes',
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson("/api/inspections/{$inspection->id}");

            $response->assertOk()
                ->assertJson([
                    'inspection' => [
                        'id' => $inspection->id,
                        'status' => 'pending',
                        'notes' => 'Test inspection notes',
                    ]
                ]);
        });
    }

    public function test_can_complete_inspection()
    {
        $this->organization->run(function () {
            $inspection = Inspection::create([
                'asset_id' => $this->asset->id,
                'template_id' => $this->template->id,
                'inspector_id' => $this->user->id,
                'scheduled_at' => now()->addHour(),
                'status' => 'pending',
            ]);

            Sanctum::actingAs($this->user);

            $responses = [
                [
                    'question_id' => 1,
                    'answer' => 'Pass',
                    'notes' => 'Tires look good',
                ],
                [
                    'question_id' => 2,
                    'answer' => 'Pass',
                    'notes' => 'All lights working',
                ]
            ];

            $response = $this->postJson("/api/inspections/{$inspection->id}/complete", [
                'responses' => $responses,
                'notes' => 'Inspection completed successfully',
                'location' => [
                    'latitude' => 40.7128,
                    'longitude' => -74.0060,
                ]
            ]);

            $response->assertOk()
                ->assertJson([
                    'message' => 'Inspection completed successfully'
                ]);

            // Verify inspection was updated
            $inspection->refresh();
            $this->assertEquals('completed', $inspection->status);
            $this->assertNotNull($inspection->completed_at);
        });
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/inspections');

        $response->assertUnauthorized();
    }

    public function test_cannot_access_other_organization_inspections()
    {
        // Create another organization
        $otherOrg = Organization::create([
            'id' => 'other-org-456',
            'name' => 'Other Organization',
            'slug' => 'other-org',
            'active' => true,
        ]);

        $otherInspectionId = null;

        // Create inspection in the other organization
        $otherOrg->run(function () use (&$otherInspectionId) {
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

            $otherAsset = Asset::create([
                'name' => 'Other Vehicle',
                'asset_id' => 'OTHER-001',
                'type' => 'vehicle',
                'active' => true,
                'qr_code' => 'QR-OTHER001',
            ]);

            $otherTemplate = ChecklistTemplate::create([
                'name' => 'Other Template',
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
                'created_by' => $otherUser->id,
            ]);

            $otherInspection = Inspection::create([
                'asset_id' => $otherAsset->id,
                'template_id' => $otherTemplate->id,
                'inspector_id' => $otherUser->id,
                'scheduled_at' => now()->addHour(),
                'status' => 'pending',
            ]);

            $otherInspectionId = $otherInspection->id;
        });

        // Try to access from our organization
        $this->organization->run(function () use ($otherInspectionId) {
            Sanctum::actingAs($this->user);

            $response = $this->getJson("/api/inspections/{$otherInspectionId}");

            $response->assertNotFound();
        });
    }
}
