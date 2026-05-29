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
        Schema::table('websites', function (Blueprint $table) {
            $table->string('google_verification_method', 20)->default('meta_tag')->after('google_verification');
            $table->string('google_verification_file', 100)->nullable()->after('google_verification_method');
            $table->text('google_verification_file_content')->nullable()->after('google_verification_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn([
                'google_verification_method',
                'google_verification_file',
                'google_verification_file_content',
            ]);
        });
    }
};
