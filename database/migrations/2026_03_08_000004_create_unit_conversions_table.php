<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_conversions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('from_unit_id')->constrained('units_of_measure')->cascadeOnDelete();
            $table->foreignId('to_unit_id')->constrained('units_of_measure')->cascadeOnDelete();
            $table->decimal('factor', 20, 10);
            $table->timestamps();

            $table->unique(['tenant_id', 'from_unit_id', 'to_unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
