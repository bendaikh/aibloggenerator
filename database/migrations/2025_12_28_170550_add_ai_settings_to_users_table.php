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
        Schema::table('users', function (Blueprint $table) {
            $table->text('openai_api_key')->nullable()->after('role');
            $table->string('ai_model')->default('gpt-4o')->after('openai_api_key');
            $table->string('ai_default_tone')->default('conversational')->after('ai_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['openai_api_key', 'ai_model', 'ai_default_tone']);
        });
    }
};
