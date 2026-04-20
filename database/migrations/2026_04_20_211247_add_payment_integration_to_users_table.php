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
            // Stripe Integration
            $table->text('stripe_publishable_key')->nullable()->after('ideogram_api_key');
            $table->text('stripe_secret_key')->nullable()->after('stripe_publishable_key');
            $table->text('stripe_webhook_secret')->nullable()->after('stripe_secret_key');
            
            // PayPal Integration
            $table->text('paypal_client_id')->nullable()->after('stripe_webhook_secret');
            $table->text('paypal_client_secret')->nullable()->after('paypal_client_id');
            $table->enum('paypal_mode', ['sandbox', 'live'])->default('sandbox')->after('paypal_client_secret');
            
            // Payment Settings
            $table->boolean('payments_enabled')->default(false)->after('paypal_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_publishable_key',
                'stripe_secret_key',
                'stripe_webhook_secret',
                'paypal_client_id',
                'paypal_client_secret',
                'paypal_mode',
                'payments_enabled',
            ]);
        });
    }
};
