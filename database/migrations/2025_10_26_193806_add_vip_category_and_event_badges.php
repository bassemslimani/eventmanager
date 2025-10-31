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
        Schema::create('event_badge_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->enum('category', ['exhibitor', 'guest', 'organizer', 'visitor'])->default('exhibitor');

            // Badge Template Images
            $table->string('front_template')->nullable();
            $table->string('back_template')->nullable();

            // Layout Configuration (JSON)
            $table->json('front_layout')->nullable()->comment('Position and styling of elements on front');
            $table->json('back_layout')->nullable()->comment('Position and styling of elements on back');

            // Badge Settings
            $table->text('terms_and_conditions')->nullable();
            $table->string('badge_size')->default('standard')->comment('standard, large, small');
            $table->integer('badge_width')->default(400)->comment('Badge width in pixels');
            $table->integer('badge_height')->default(600)->comment('Badge height in pixels');

            // Visual Settings
            $table->string('font_family')->default('Inter');
            $table->string('primary_color')->default('#000000');
            $table->string('secondary_color')->default('#666666');
            $table->boolean('show_qr_code')->default(true);
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_category_badge')->default(true);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['event_id', 'category']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_badge_templates');
    }
};
