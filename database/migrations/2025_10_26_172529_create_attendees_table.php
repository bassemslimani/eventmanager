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
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('type', ['exhibitor', 'guest', 'organizer'])->default('guest');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('email')->unique();
            $table->string('mobile')->nullable();
            $table->string('company')->nullable();
            $table->string('company_ar')->nullable();
            $table->enum('category', ['freelancer', 'company'])->nullable();
            $table->string('role')->nullable(); // For organizers
            $table->string('department')->nullable(); // For organizers
            $table->string('qr_code')->unique();
            $table->uuid('qr_uuid')->unique();
            $table->string('badge_url')->nullable();
            $table->timestamp('badge_generated_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->unsignedBigInteger('checked_in_by')->nullable();
            $table->timestamp('welcome_email_sent_at')->nullable();
            $table->json('preferences')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['type', 'email']);
            $table->index('qr_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
