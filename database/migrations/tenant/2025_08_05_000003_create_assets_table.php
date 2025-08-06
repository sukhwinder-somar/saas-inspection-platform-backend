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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_id')->unique();
            $table->string('type'); // Vehicle, Machinery, Equipment, Building, Tool
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('qr_code')->unique();
            $table->json('custom_fields')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->date('next_service_due')->nullable();
            $table->date('insurance_renewal')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['type', 'active']);
            $table->index(['next_service_due', 'active']);
            $table->index(['registration_expiry', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
