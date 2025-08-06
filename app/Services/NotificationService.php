<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Services\AwsSnsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    private $awsSnsService;

    public function __construct(AwsSnsService $awsSnsService)
    {
        $this->awsSnsService = $awsSnsService;
    }

    /**
     * Send notification to user(s)
     */
    public function send(
        array $userIds,
        string $title,
        string $message,
        string $type = 'info',
        array $channels = ['database', 'email'],
        array $data = []
    ): bool {
        $success = true;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            // Save to database
            if (in_array('database', $channels)) {
                $this->createDatabaseNotification($user, $title, $message, $type, $data);
            }

            // Send email
            if (in_array('email', $channels) && $user->email) {
                $emailSent = $this->sendEmailNotification($user, $title, $message);
                if (!$emailSent) {
                    $success = false;
                }
            }

            // Send SMS
            if (in_array('sms', $channels) && isset($user->phone_number)) {
                $smsSent = $this->sendSmsNotification($user, $title, $message);
                if (!$smsSent) {
                    $success = false;
                }
            }

            // Send Push Notification
            if (in_array('push', $channels)) {
                $pushSent = $this->sendPushNotification($user, $title, $message);
                if (!$pushSent) {
                    $success = false;
                }
            }

            // Send Slack notification
            if (in_array('slack', $channels)) {
                $slackSent = $this->sendSlackNotification($user, $title, $message);
                if (!$slackSent) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Send system-wide notification
     */
    public function sendSystemNotification(
        string $title,
        string $message,
        string $type = 'system',
        array $channels = ['sns']
    ): bool {
        if (in_array('sns', $channels)) {
            return $this->awsSnsService->sendNotification($message, $title);
        }

        return true;
    }

    /**
     * Send inspection overdue notifications
     */
    public function sendInspectionOverdueNotifications(): void
    {
        $overdueInspections = \App\Models\Inspection::where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->with(['asset', 'assignedUser'])
            ->get();

        foreach ($overdueInspections as $inspection) {
            if ($inspection->assignedUser) {
                $this->send(
                    [$inspection->assignedUser->id],
                    'Inspection Overdue',
                    "Inspection for {$inspection->asset->name} is overdue. Due date was {$inspection->due_date->format('Y-m-d')}.",
                    'warning',
                    ['database', 'email', 'push'],
                    [
                        'inspection_id' => $inspection->id,
                        'asset_id' => $inspection->asset_id,
                        'type' => 'inspection_overdue'
                    ]
                );
            }
        }
    }

    /**
     * Send asset status change notifications
     */
    public function sendAssetStatusChangeNotification($asset, $oldStatus, $newStatus): void
    {
        $assignedUsers = $asset->assignedUsers;
        
        if ($assignedUsers->count() > 0) {
            $userIds = $assignedUsers->pluck('id')->toArray();
            
            $this->send(
                $userIds,
                'Asset Status Changed',
                "Asset {$asset->name} status changed from {$oldStatus} to {$newStatus}.",
                'info',
                ['database', 'email'],
                [
                    'asset_id' => $asset->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'type' => 'asset_status_change'
                ]
            );
        }
    }

    /**
     * Create database notification
     */
    private function createDatabaseNotification(User $user, string $title, string $message, string $type, array $data): void
    {
        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'read_at' => null,
        ]);
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(User $user, string $title, string $message): bool
    {
        try {
            Mail::send('emails.notification', [
                'user' => $user,
                'title' => $title,
                'message' => $message,
            ], function ($m) use ($user, $title) {
                $m->to($user->email, $user->name)->subject($title);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification(User $user, string $title, string $message): bool
    {
        return $this->awsSnsService->sendSms(
            $user->phone_number,
            "{$title}: {$message}"
        );
    }

    /**
     * Send push notification via SNS
     */
    private function sendPushNotification(User $user, string $title, string $message): bool
    {
        //TODO This would integrate with mobile push notification services
        //TODO For now, we'll use SNS topic for the user's organization
        return $this->awsSnsService->sendNotification(
            $message,
            $title,
            $user->organization->sns_topic_arn ?? null
        );
    }

    /**
     * Send Slack notification
     */
    private function sendSlackNotification(User $user, string $title, string $message): bool
    {
        try {
            //TODO Implementation for Slack webhook would go here
            //TODO For now, we'll log it
            Log::info('Slack notification would be sent', [
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send Slack notification', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
