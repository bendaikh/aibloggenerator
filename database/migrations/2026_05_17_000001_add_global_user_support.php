<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_global_user')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_global_user')->default(false)->after('status');
            });
        }

        if (!Schema::hasTable('global_user_secondary_users')) {
            Schema::create('global_user_secondary_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('global_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('secondary_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['global_user_id', 'secondary_user_id'], 'global_user_secondary_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('global_user_secondary_users');

        if (Schema::hasColumn('users', 'is_global_user')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_global_user');
            });
        }
    }
};
