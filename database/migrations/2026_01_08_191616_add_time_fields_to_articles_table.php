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
        Schema::table('articles', function (Blueprint $table) {
            $table->string('prep_time')->nullable()->after('content');
            $table->string('cook_time')->nullable()->after('prep_time');
            $table->string('rest_time')->nullable()->after('cook_time');
            $table->string('total_time')->nullable()->after('rest_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['prep_time', 'cook_time', 'rest_time', 'total_time']);
        });
    }
};
