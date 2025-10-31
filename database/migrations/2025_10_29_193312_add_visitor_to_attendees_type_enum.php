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
        // Add 'visitor' to the type ENUM in attendees table
        \DB::statement("ALTER TABLE attendees MODIFY COLUMN type ENUM('exhibitor', 'guest', 'organizer', 'visitor') DEFAULT 'guest'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'visitor' from the type ENUM in attendees table
        \DB::statement("ALTER TABLE attendees MODIFY COLUMN type ENUM('exhibitor', 'guest', 'organizer') DEFAULT 'guest'");
    }
};
