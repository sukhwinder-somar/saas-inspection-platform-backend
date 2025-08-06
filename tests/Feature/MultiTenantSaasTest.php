<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;

class MultiTenantSaasTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_be_created(): void
    {
        $organization = Organization::create([
            'id' => 'org-test-123',
            'name' => 'Test Organization',
            'slug' => 'test-org',
            'active' => true,
        ]);

        $this->assertDatabaseHas('organizations', [
            'id' => 'org-test-123',
            'name' => 'Test Organization',
            'slug' => 'test-org',
            'active' => true,
        ]);
    }

    public function test_user_belongs_to_organization(): void
    {
        $organization = Organization::create([
            'id' => 'org-test-123',
            'name' => 'Test Organization',
            'slug' => 'test-org',
            'active' => true,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $organization->id,
        ]);

        $this->assertEquals($organization->id, $user->tenant_id);
        $this->assertInstanceOf(Organization::class, $user->tenant);
    }

    public function test_organization_has_users(): void
    {
        $organization = Organization::create([
            'id' => 'org-test-123',
            'name' => 'Test Organization',
            'slug' => 'test-org',
            'active' => true,
        ]);

        $user1 = User::create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $organization->id,
        ]);

        $user2 = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $organization->id,
        ]);

        $this->assertCount(2, $organization->users);
        $this->assertTrue($organization->users->contains($user1));
        $this->assertTrue($organization->users->contains($user2));
    }
}
