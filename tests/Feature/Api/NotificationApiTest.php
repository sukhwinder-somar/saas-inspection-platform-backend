<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\Notification;
use App\Models\Asset;
use Laravel\Sanctum\Sanctum;

class NotificationApiTest extends TestCase
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

    public function test_can_list_notifications()
    {
        $this->organization->run(function () {
            // Create test notifications
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_overdue',
                'title' => 'Inspection Overdue',
                'message' => 'Asset VEH-001 inspection is overdue',
                'data' => [
                    'asset_id' => 1,
                    'days_overdue' => 5,
                ],
                'read_at' => null,
                'created_at' => now()->subHours(2),
            ]);

            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_reminder',
                'title' => 'Inspection Reminder',
                'message' => 'Asset EQP-002 inspection due tomorrow',
                'data' => [
                    'asset_id' => 2,
                    'due_date' => now()->addDay()->format('Y-m-d'),
                ],
                'read_at' => now()->subHour(),
                'created_at' => now()->subHours(1),
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/notifications');

            $response->assertOk()
                ->assertJsonStructure([
                    'notifications' => [
                        'data' => [
                            '*' => [
                                'id',
                                'type',
                                'title',
                                'message',
                                'data',
                                'read_at',
                                'created_at',
                            ]
                        ]
                    ]
                ]);

            $data = $response->json('notifications.data');
            $this->assertCount(2, $data);
        });
    }

    public function test_can_filter_unread_notifications()
    {
        $this->organization->run(function () {
            // Create read notification
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_reminder',
                'title' => 'Read Notification',
                'message' => 'This notification has been read',
                'data' => [],
                'read_at' => now()->subHour(),
                'created_at' => now()->subHours(2),
            ]);

            // Create unread notification
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_overdue',
                'title' => 'Unread Notification',
                'message' => 'This notification is unread',
                'data' => [],
                'read_at' => null,
                'created_at' => now()->subHour(),
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/notifications?unread=true');

            $response->assertOk();

            $data = $response->json('notifications.data');
            $this->assertCount(1, $data);
            $this->assertEquals('Unread Notification', $data[0]['title']);
        });
    }

    public function test_can_mark_notification_as_read()
    {
        $this->organization->run(function () {
            $notification = Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_overdue',
                'title' => 'Test Notification',
                'message' => 'Test message',
                'data' => [],
                'read_at' => null,
                'created_at' => now(),
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->patchJson("/api/notifications/{$notification->id}/read");

            $response->assertOk()
                ->assertJson([
                    'message' => 'Notification marked as read'
                ]);

            // Verify notification was marked as read
            $notification->refresh();
            $this->assertNotNull($notification->read_at);
        });
    }

    public function test_can_mark_all_notifications_as_read()
    {
        $this->organization->run(function () {
            // Create multiple unread notifications
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_overdue',
                'title' => 'Notification 1',
                'message' => 'Message 1',
                'data' => [],
                'read_at' => null,
                'created_at' => now()->subHours(2),
            ]);

            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_reminder',
                'title' => 'Notification 2',
                'message' => 'Message 2',
                'data' => [],
                'read_at' => null,
                'created_at' => now()->subHour(),
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->patchJson('/api/notifications/mark-all-read');

            $response->assertOk()
                ->assertJson([
                    'message' => 'All notifications marked as read'
                ]);

            // Verify all notifications were marked as read
            $unreadCount = Notification::where('user_id', $this->user->id)
                ->whereNull('read_at')
                ->count();

            $this->assertEquals(0, $unreadCount);
        });
    }

    public function test_can_get_unread_count()
    {
        $this->organization->run(function () {
            // Create notifications with mixed read status
            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_overdue',
                'title' => 'Unread 1',
                'message' => 'Message 1',
                'data' => [],
                'read_at' => null,
                'created_at' => now(),
            ]);

            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_reminder',
                'title' => 'Unread 2',
                'message' => 'Message 2',
                'data' => [],
                'read_at' => null,
                'created_at' => now(),
            ]);

            Notification::create([
                'user_id' => $this->user->id,
                'type' => 'inspection_completed',
                'title' => 'Read 1',
                'message' => 'Message 3',
                'data' => [],
                'read_at' => now()->subHour(),
                'created_at' => now(),
            ]);

            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/notifications/unread-count');

            $response->assertOk()
                ->assertJson([
                    'unread_count' => 2
                ]);
        });
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/notifications');

        $response->assertUnauthorized();
    }

    public function test_notification_not_found_returns_404()
    {
        $this->organization->run(function () {
            Sanctum::actingAs($this->user);

            $response = $this->patchJson('/api/notifications/999/read');

            $response->assertNotFound();
        });
    }

    public function test_cannot_access_other_user_notifications()
    {
        $this->organization->run(function () {
            // Create another user
            $otherUser = User::create([
                'name' => 'Other User',
                'email' => 'other@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            // Create notification for other user
            $otherNotification = Notification::create([
                'user_id' => $otherUser->id,
                'type' => 'inspection_overdue',
                'title' => 'Other User Notification',
                'message' => 'This belongs to another user',
                'data' => [],
                'read_at' => null,
                'created_at' => now(),
            ]);

            // Try to access as our user
            Sanctum::actingAs($this->user);

            $response = $this->patchJson("/api/notifications/{$otherNotification->id}/read");

            $response->assertNotFound();
        });
    }

    public function test_cannot_access_other_organization_notifications()
    {
        // Create another organization
        $otherOrg = Organization::create([
            'id' => 'other-org-456',
            'name' => 'Other Organization',
            'slug' => 'other-org',
            'active' => true,
        ]);

        $otherNotificationId = null;

        // Create notification in the other organization
        $otherOrg->run(function () use (&$otherNotificationId) {
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

            $otherNotification = Notification::create([
                'user_id' => $otherUser->id,
                'type' => 'inspection_overdue',
                'title' => 'Other Org Notification',
                'message' => 'This belongs to another organization',
                'data' => [],
                'read_at' => null,
                'created_at' => now(),
            ]);

            $otherNotificationId = $otherNotification->id;
        });

        // Try to access from our organization
        $this->organization->run(function () use ($otherNotificationId) {
            Sanctum::actingAs($this->user);

            $response = $this->patchJson("/api/notifications/{$otherNotificationId}/read");

            $response->assertNotFound();
        });
    }
}
