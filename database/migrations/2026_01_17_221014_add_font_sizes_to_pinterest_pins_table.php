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
            $table->integer('headline_font_size')->default(28)->after('headline_font');
            $table->integer('subheadline_font_size')->default(22)->after('subheadline_font');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pinterest_pins', function (Blueprint $table) {
            $table->dropColumn(['headline_font_size', 'subheadline_font_size']);
        });
    }
};
