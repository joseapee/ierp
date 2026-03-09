<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'manufactured' to the type enum.
        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('standard','variable','service','bundle','manufactured') NOT NULL DEFAULT 'standard'");

        // Add pricing strategy columns.
        Schema::table('products', function (Blueprint $table): void {
            $table->enum('pricing_mode', ['manual', 'percentage_markup', 'fixed_profit'])
                ->default('manual')
                ->after('sell_price');
            $table->decimal('markup_percentage', 8, 4)->nullable()->after('pricing_mode');
            $table->decimal('profit_amount', 15, 4)->nullable()->after('markup_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['pricing_mode', 'markup_percentage', 'profit_amount']);
        });

        DB::statement("ALTER TABLE products MODIFY COLUMN type ENUM('standard','variable','service','bundle') NOT NULL DEFAULT 'standard'");
    }
};
