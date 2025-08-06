<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessScheduledNotifications implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        // Send overdue inspection notifications
        $notificationService->sendInspectionOverdueNotifications();

        // You can add more scheduled notification types here
        // Example: sendMaintenanceReminders(), sendComplianceAlerts(), etc.
    }
}
