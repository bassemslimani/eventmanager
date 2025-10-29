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
        Schema::table('import_logs', function (Blueprint $table) {
            // Change type from enum to string to support different import types
            $table->string('type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            // Revert back to original enum (though this might fail if data exists)
            $table->enum('type', ['exhibitor', 'guest', 'organizer'])->change();
        });
    }
};
