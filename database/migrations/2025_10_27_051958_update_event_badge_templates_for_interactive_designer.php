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
        Schema::table('event_badge_templates', function (Blueprint $table) {
            // Change dimensions to use cm (8.5cm x 12.5cm standard badge size)
            $table->decimal('badge_width_cm', 5, 2)->default(8.5)->after('badge_height');
            $table->decimal('badge_height_cm', 5, 2)->default(12.5)->after('badge_width_cm');

            // Store interactive layout elements with positions
            $table->json('elements')->nullable()->after('back_layout');

            // Add canvas settings
            $table->string('measurement_unit')->default('cm')->after('badge_height_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_badge_templates', function (Blueprint $table) {
            $table->dropColumn(['badge_width_cm', 'badge_height_cm', 'elements', 'measurement_unit']);
        });
    }
};
