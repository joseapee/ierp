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
        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreignId('plan_id')->nullable()->after('domain')->constrained('plans')->nullOnDelete();
            $table->dateTime('onboarding_completed_at')->nullable()->after('subscription_ends_at');
            $table->dateTime('setup_completed_at')->nullable()->after('onboarding_completed_at');
            $table->string('industry')->nullable()->after('setup_completed_at');
            $table->string('currency')->default('NGN')->after('industry');
            $table->string('timezone')->default('Africa/Lagos')->after('currency');
            $table->string('country')->nullable()->after('timezone');
            $table->string('city')->nullable()->after('country');
            $table->text('address')->nullable()->after('city');
            $table->string('phone')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropForeign(['plan_id']);
            $table->dropColumn([
                'plan_id',
                'onboarding_completed_at',
                'setup_completed_at',
                'industry',
                'currency',
                'timezone',
                'country',
                'city',
                'address',
                'phone',
            ]);
        });
    }
};
