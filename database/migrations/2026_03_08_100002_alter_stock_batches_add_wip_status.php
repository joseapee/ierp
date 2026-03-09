<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_batches MODIFY COLUMN status ENUM('available','reserved','quarantine','expired','wip') NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_batches MODIFY COLUMN status ENUM('available','reserved','quarantine','expired') NOT NULL DEFAULT 'available'");
    }
};
