<?php

namespace Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\ChecklistTemplate;
use App\Filament\Resources\ChecklistTemplateResource;
use Livewire\Livewire;
use Filament\Actions\DeleteAction;

class ChecklistTemplateResourceTest extends TestCase
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

    public function test_can_render_template_list_page()
    {
        $this->organization->run(function () {
            $this->actingAs($this->user);

            Livewire::test(ChecklistTemplateResource\Pages\ListChecklistTemplates::class)
                ->assertSuccessful();
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
                'sections' => [],
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

            $this->actingAs($this->user);

            Livewire::test(ChecklistTemplateResource\Pages\ListChecklistTemplates::class)
                ->assertCanSeeTableRecords([
                    ChecklistTemplate::where('name', 'Vehicle Inspection')->first(),
                    ChecklistTemplate::where('name', 'Equipment Check')->first(),
                ]);
        });
    }

    public function test_can_create_template()
    {
        $this->organization->run(function () {
            $this->actingAs($this->user);

            $newData = [
                'name' => 'New Inspection Template',
                'description' => 'A test inspection template',
                'asset_types' => ['vehicle', 'equipment'],
                'sections' => [
                    [
                        'name' => 'Safety Check',
                        'questions' => [
                            [
                                'question' => 'Is the equipment safe to use?',
                                'type' => 'radio',
                                'options' => ['Yes', 'No'],
                                'required' => true,
                            ],
                            [
                                'question' => 'Any visible damage?',
                                'type' => 'text',
                                'required' => false,
                            ]
                        ]
                    ]
                ],
                'active' => true,
            ];

            Livewire::test(ChecklistTemplateResource\Pages\CreateChecklistTemplate::class)
                ->fillForm($newData)
                ->call('create')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas('checklist_templates', [
                'name' => 'New Inspection Template',
                'description' => 'A test inspection template',
                'active' => true,
                'created_by' => $this->user->id,
            ]);
        });
    }

    public function test_can_edit_template()
    {
        $this->organization->run(function () {
            $template = ChecklistTemplate::create([
                'name' => 'Original Template',
                'description' => 'Original description',
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            $this->actingAs($this->user);

            $newData = [
                'name' => 'Updated Template',
                'description' => 'Updated description',
                'asset_types' => ['vehicle', 'equipment'],
            ];

            Livewire::test(ChecklistTemplateResource\Pages\EditChecklistTemplate::class, [
                'record' => $template->getRouteKey(),
            ])
                ->fillForm($newData)
                ->call('save')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas('checklist_templates', [
                'id' => $template->id,
                'name' => 'Updated Template',
                'description' => 'Updated description',
            ]);
        });
    }

    public function test_can_delete_template()
    {
        $this->organization->run(function () {
            $template = ChecklistTemplate::create([
                'name' => 'Template to Delete',
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            $this->actingAs($this->user);

            Livewire::test(ChecklistTemplateResource\Pages\EditChecklistTemplate::class, [
                'record' => $template->getRouteKey(),
            ])
                ->callAction(DeleteAction::class);

            $this->assertModelMissing($template);
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

            $this->actingAs($this->user);

            Livewire::test(ChecklistTemplateResource\Pages\ViewChecklistTemplate::class, [
                'record' => $template->getRouteKey(),
            ])
                ->callAction('duplicate', [
                    'name' => 'Duplicated Template'
                ])
                ->assertHasNoActionErrors();

            $this->assertDatabaseHas('checklist_templates', [
                'name' => 'Duplicated Template',
                'description' => 'Original description',
                'created_by' => $this->user->id,
            ]);

            // Verify sections were copied
            $duplicatedTemplate = ChecklistTemplate::where('name', 'Duplicated Template')->first();
            $this->assertEquals($template->sections, $duplicatedTemplate->sections);
        });
    }

    public function test_can_filter_templates_by_asset_type()
    {
        $this->organization->run(function () {
            ChecklistTemplate::create([
                'name' => 'Vehicle Template',
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            ChecklistTemplate::create([
                'name' => 'Equipment Template',
                'asset_types' => ['equipment'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            $this->actingAs($this->user);

            Livewire::test(ChecklistTemplateResource\Pages\ListChecklistTemplates::class)
                ->filterTable('asset_types', 'vehicle')
                ->assertCanSeeTableRecords([
                    ChecklistTemplate::where('name', 'Vehicle Template')->first(),
                ])
                ->assertCanNotSeeTableRecords([
                    ChecklistTemplate::where('name', 'Equipment Template')->first(),
                ]);
        });
    }

    public function test_can_search_templates()
    {
        $this->organization->run(function () {
            ChecklistTemplate::create([
                'name' => 'Search Test Template',
                'description' => 'A template for searching',
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            ChecklistTemplate::create([
                'name' => 'Other Template',
                'description' => 'Another template',
                'asset_types' => ['equipment'],
                'sections' => [],
                'active' => true,
                'created_by' => $this->user->id,
            ]);

            $this->actingAs($this->user);

            Livewire::test(ChecklistTemplateResource\Pages\ListChecklistTemplates::class)
                ->searchTable('Search Test')
                ->assertCanSeeTableRecords([
                    ChecklistTemplate::where('name', 'Search Test Template')->first(),
                ])
                ->assertCanNotSeeTableRecords([
                    ChecklistTemplate::where('name', 'Other Template')->first(),
                ]);
        });
    }

    public function test_template_name_is_required()
    {
        $this->organization->run(function () {
            $this->actingAs($this->user);

            $invalidData = [
                'name' => '', // Empty name
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
            ];

            Livewire::test(ChecklistTemplateResource\Pages\CreateChecklistTemplate::class)
                ->fillForm($invalidData)
                ->call('create')
                ->assertHasFormErrors(['name']);
        });
    }

    public function test_asset_types_is_required()
    {
        $this->organization->run(function () {
            $this->actingAs($this->user);

            $invalidData = [
                'name' => 'Test Template',
                'asset_types' => [], // Empty asset types
                'sections' => [],
                'active' => true,
            ];

            Livewire::test(ChecklistTemplateResource\Pages\CreateChecklistTemplate::class)
                ->fillForm($invalidData)
                ->call('create')
                ->assertHasFormErrors(['asset_types']);
        });
    }

    public function test_created_by_is_set_automatically()
    {
        $this->organization->run(function () {
            $this->actingAs($this->user);

            $newData = [
                'name' => 'Auto Created Template',
                'asset_types' => ['vehicle'],
                'sections' => [],
                'active' => true,
            ];

            Livewire::test(ChecklistTemplateResource\Pages\CreateChecklistTemplate::class)
                ->fillForm($newData)
                ->call('create')
                ->assertHasNoFormErrors();

            $template = ChecklistTemplate::where('name', 'Auto Created Template')->first();
            $this->assertEquals($this->user->id, $template->created_by);
        });
    }
}
