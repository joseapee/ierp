<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wip_inventory', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->string('current_stage')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 4)->default(0);
            $table->enum('status', ['in_progress', 'completed', 'scrapped'])->default('in_progress');
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['production_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wip_inventory');
    }
};
