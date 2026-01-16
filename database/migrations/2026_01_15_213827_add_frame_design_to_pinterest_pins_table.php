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
        Schema::table('pinterest_pins', function (Blueprint $table) {
            // Frame design selection
            $table->string('frame_design')->default('simple_center')->after('overlay_opacity');
            // Additional frame-specific settings stored as JSON
            $table->json('frame_settings')->nullable()->after('frame_design');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pinterest_pins', function (Blueprint $table) {
            $table->dropColumn(['frame_design', 'frame_settings']);
        });
    }
};
