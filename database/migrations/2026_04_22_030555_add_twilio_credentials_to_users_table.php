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
            $table->text('twilio_sid')->nullable()->after('paypal_mode');
            $table->text('twilio_auth_token')->nullable()->after('twilio_sid');
            $table->string('twilio_whatsapp_from', 20)->nullable()->after('twilio_auth_token');
            $table->string('admin_whatsapp_number', 20)->nullable()->after('twilio_whatsapp_from');
            $table->boolean('security_alerts_enabled')->default(true)->after('admin_whatsapp_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'twilio_sid',
                'twilio_auth_token',
                'twilio_whatsapp_from',
                'admin_whatsapp_number',
                'security_alerts_enabled',
            ]);
        });
    }
};
