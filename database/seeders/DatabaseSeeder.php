<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, create roles and permissions
        $this->call(RolePermissionSeeder::class);

        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.test',
            'password' => Hash::make('admin'),
            'is_super_admin' => true,
        ]);

        // Assign super admin role to the default admin user
        $user->assignRole('super_admin');

        Post::factory()
            ->count(25)
            ->create();

        Notification::make()
            ->title('Welcome to Filament SaaS')
            ->body('Your multi-tenant SaaS application is ready! You can now create organizations and manage tenants.')
            ->success()
            ->sendToDatabase($user);
    }
}
