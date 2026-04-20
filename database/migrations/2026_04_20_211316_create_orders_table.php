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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            
            // Customer Information
            $table->string('customer_email');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Payment Information
            $table->enum('payment_provider', ['stripe', 'paypal'])->nullable();
            $table->string('payment_id')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'])->default('pending');
            
            // Order Details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->json('product_snapshot')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'paid', 'fulfilled', 'cancelled', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            
            // Additional Info
            $table->json('billing_address')->nullable();
            $table->json('meta_data')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['website_id', 'status']);
            $table->index('payment_id');
            $table->index('customer_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
