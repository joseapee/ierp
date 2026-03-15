<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('social_provider')->nullable()->after('password');
            $table->string('social_id')->nullable()->after('social_provider');

            $table->index(['social_provider', 'social_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['social_provider', 'social_id']);
            $table->dropColumn(['social_provider', 'social_id']);
        });
    }
};
