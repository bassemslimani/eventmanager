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
        Schema::create('badge_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['exhibitor', 'guest', 'organizer'])->unique();
            $table->string('name');
            $table->string('background_image')->nullable();
            $table->string('overlay_color')->default('#10B981');
            $table->integer('overlay_opacity')->default(85);
            $table->boolean('glass_effect')->default(true);
            $table->string('gradient_direction')->default('135deg');
            $table->string('font_family')->default('Inter');
            $table->json('layout_config')->nullable();
            $table->text('css_overrides')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_templates');
    }
};
