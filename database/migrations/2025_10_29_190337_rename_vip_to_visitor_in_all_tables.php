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
        // Update existing attendees with type 'vip' to 'visitor'
        \DB::table('attendees')
            ->where('type', 'vip')
            ->update(['type' => 'visitor']);

        // Update existing event_badge_templates with category 'vip' to 'visitor'
        \DB::table('event_badge_templates')
            ->where('category', 'vip')
            ->update(['category' => 'visitor']);

        // Alter the enum column in event_badge_templates table
        \DB::statement("ALTER TABLE event_badge_templates MODIFY COLUMN category ENUM('exhibitor', 'guest', 'organizer', 'visitor') DEFAULT 'exhibitor'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert attendees with type 'visitor' back to 'vip'
        \DB::table('attendees')
            ->where('type', 'visitor')
            ->update(['type' => 'vip']);

        // Revert event_badge_templates with category 'visitor' back to 'vip'
        \DB::table('event_badge_templates')
            ->where('category', 'visitor')
            ->update(['category' => 'vip']);

        // Revert the enum column in event_badge_templates table
        \DB::statement("ALTER TABLE event_badge_templates MODIFY COLUMN category ENUM('exhibitor', 'guest', 'organizer', 'vip') DEFAULT 'exhibitor'");
    }
};
