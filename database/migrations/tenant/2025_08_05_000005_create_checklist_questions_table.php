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
        Schema::create('checklist_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('checklist_templates')->onDelete('cascade');
            $table->string('question');
            $table->enum('type', ['radio', 'checkbox', 'text', 'number', 'date', 'photo', 'signature']);
            $table->json('options')->nullable(); // For radio/checkbox types
            $table->boolean('required')->default(true);
            $table->json('conditional_logic')->nullable(); // When to show this question
            $table->string('notification_message')->nullable(); // Custom notification for specific answers
            $table->integer('order');
            $table->string('section'); // Which section this question belongs to
            $table->timestamps();

            $table->index(['template_id', 'section', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_questions');
    }
};
