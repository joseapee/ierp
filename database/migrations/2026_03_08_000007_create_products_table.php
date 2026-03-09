<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('base_unit_id')->constrained('units_of_measure')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku');
            $table->enum('type', ['standard', 'variable', 'service', 'bundle', 'manufactured'])->default('standard');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('image')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('cost_price', 15, 4)->default(0);
            $table->decimal('sell_price', 15, 4)->default(0);
            $table->enum('pricing_mode', ['manual', 'percentage_markup', 'fixed_profit'])->default('manual');
            $table->decimal('markup_percentage', 8, 4)->nullable();
            $table->decimal('profit_amount', 15, 4)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('valuation_method', ['fifo', 'lifo', 'weighted_average', 'standard'])->default('weighted_average');
            $table->decimal('reorder_level', 15, 4)->default(0);
            $table->decimal('reorder_quantity', 15, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_purchasable')->default(true);
            $table->boolean('is_sellable')->default(true);
            $table->boolean('is_stockable')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'sku']);
            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'brand_id']);
            $table->index(['tenant_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
