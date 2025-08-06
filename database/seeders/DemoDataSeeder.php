<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Asset;
use App\Models\ChecklistTemplate;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate unique ID for this seeding run
        $timestamp = time();
        $uniqueId = 'demo-' . $timestamp;
        
        // Create a demo organization (tenant)
        $organization = Organization::create([
            'id' => $uniqueId,
            'name' => 'Demo Manufacturing Co.',
            'slug' => 'demo-manufacturing-co-' . $timestamp,
            'subdomain' => 'demo' . $timestamp,
            'data' => [
                'status' => 'active',
            ],
            'settings' => [
                'timezone' => 'UTC',
                'currency' => 'USD',
                'date_format' => 'Y-m-d',
            ],
        ]);

        // Manually create the domain for the tenant
        $domain = $organization->subdomain . '.test';
        $organization->domains()->create(['domain' => $domain]);

        // Switch to tenant context
        tenancy()->initialize($organization);

        // Create roles and permissions for this tenant
        $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $operatorRole = \Spatie\Permission\Models\Role::create(['name' => 'operator']);
        
        // Create basic permissions
        $permissions = [
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
        
        // Assign all permissions to admin role
        $adminRole->givePermissionTo($permissions);
        
        // Assign limited permissions to operator role
        $operatorRole->givePermissionTo([
            'view_assets',
            'view_inspections', 
            'create_inspections',
            'edit_inspections',
        ]);

        // Create demo users with roles
        $admin = User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $operator = User::create([
            'name' => 'Demo Operator',
            'email' => 'operator@demo.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $operator->assignRole('operator');

        // Create demo checklist templates
        $safetyTemplate = ChecklistTemplate::create([
            'name' => 'Safety Inspection Checklist',
            'description' => 'Comprehensive safety inspection for industrial equipment',
            'asset_types' => ['machinery', 'equipment'],
            'sections' => [
                [
                    'id' => Str::uuid(),
                    'title' => 'Visual Inspection',
                    'description' => 'Check for visible damage, wear, or safety issues',
                    'questions' => [
                        [
                            'id' => Str::uuid(),
                            'text' => 'Are there any visible cracks or damage?',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Yes', 'No'],
                        ],
                        [
                            'id' => Str::uuid(),
                            'text' => 'Are safety guards in place and secure?',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Yes', 'No'],
                        ],
                        [
                            'id' => Str::uuid(),
                            'text' => 'Take a photo of the equipment',
                            'type' => 'photo',
                            'required' => true,
                        ],
                    ],
                ],
                [
                    'id' => Str::uuid(),
                    'title' => 'Functional Testing',
                    'description' => 'Test equipment functionality and performance',
                    'questions' => [
                        [
                            'id' => Str::uuid(),
                            'text' => 'Does the equipment start and stop properly?',
                            'type' => 'radio',
                            'required' => true,
                            'options' => ['Yes', 'No'],
                        ],
                        [
                            'id' => Str::uuid(),
                            'text' => 'Record operating temperature (°C)',
                            'type' => 'number',
                            'required' => false,
                        ],
                    ],
                ],
            ],
            'version' => 1,
            'active' => true,
            'created_by' => $admin->id,
        ]);

        // Create demo assets
        for ($i = 1; $i <= 10; $i++) {
            $assetId = "EQ-" . str_pad($i, 4, '0', STR_PAD_LEFT);
            $qrCode = Str::uuid();
            
            Asset::create([
                'asset_id' => $assetId,
                'name' => "Industrial Equipment #{$i}",
                'type' => ['Vehicle', 'Machinery', 'Equipment'][array_rand(['Vehicle', 'Machinery', 'Equipment'])],
                'make' => ['Caterpillar', 'John Deere', 'Toyota'][array_rand(['Caterpillar', 'John Deere', 'Toyota'])],
                'model' => "Model-{$i}",
                'serial_number' => "SN-{$assetId}",
                'description' => "High-performance industrial equipment for manufacturing operations",
                'location' => "Floor {$i} - Section " . chr(65 + ($i % 3)),
                'qr_code' => $qrCode,
                'registration_number' => "REG-{$assetId}",
                'registration_expiry' => now()->addYears(rand(1, 3)),
                'next_service_due' => now()->addDays(rand(30, 90)),
                'insurance_renewal' => now()->addYears(rand(1, 2)),
                'active' => true,
            ]);
        }

        // Return to central context
        tenancy()->end();

        $this->command->info('Demo data created successfully!');
        $this->command->info('================================================');
        $this->command->info('Organization: Demo Manufacturing Co. (ID: ' . $uniqueId . ')');
        $this->command->info('Domain: ' . $domain);
        $this->command->info('Admin Login: admin@demo.test / password');
        $this->command->info('Operator Login: operator@demo.test / password');
        $this->command->info('================================================');
        $this->command->info('');
        $this->command->info('🔧 TO ACCESS THE TENANT (Valet):');
        $this->command->info('1. Link this domain with Valet:');
        $this->command->info('   valet link demo' . $timestamp);
        $this->command->info('2. Visit: https://demo' . $timestamp . '.test/admin');
        $this->command->info('3. Login with: admin@demo.test / password');
        $this->command->info('');
        $this->command->info('📝 Alternative (manual hosts):');
        $this->command->info('   echo "127.0.0.1   ' . $domain . '" | sudo tee -a /etc/hosts');
        $this->command->info('   Then visit: http://' . $domain . '/admin');
        $this->command->info('================================================');
    }
}
