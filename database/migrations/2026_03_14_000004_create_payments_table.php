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
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('NGN');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->string('paystack_reference')->nullable()->unique();
            $table->string('paystack_transaction_id')->nullable();
            $table->json('metadata')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
