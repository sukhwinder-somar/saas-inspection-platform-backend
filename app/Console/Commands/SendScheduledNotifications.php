<?php

namespace App\Console\Commands;

use App\Jobs\ProcessScheduledNotifications;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled notifications (overdue inspections, maintenance reminders, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching scheduled notifications job...');

        ProcessScheduledNotifications::dispatch();

        $this->info('Scheduled notifications job dispatched successfully.');

        return 0;
    }
}
