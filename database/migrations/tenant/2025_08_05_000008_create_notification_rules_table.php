<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_event'); // inspection_overdue, critical_fail, etc.
            $table->json('conditions'); // When to trigger
            $table->json('actions'); // What actions to take
            $table->json('channels'); // email, push, sms
            $table->string('message_template');
            $table->boolean('active')->default(true);
            $table->json('schedule_settings')->nullable(); // For scheduled notifications
            $table->timestamps();

            $table->index(['trigger_event', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_rules');
    }
};
