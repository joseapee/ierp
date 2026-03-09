<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductionStage;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ProductionStageSeeder extends Seeder
{
    /**
     * Production stage templates organized by industry type.
     *
     * @var array<string, array<int, array{name: string, code: string, estimated_duration_minutes: int|null}>>
     */
    protected array $stages = [
        'fashion' => [
            ['name' => 'Cutting',           'code' => 'CUT', 'estimated_duration_minutes' => 60],
            ['name' => 'Stitching',         'code' => 'STI', 'estimated_duration_minutes' => 120],
            ['name' => 'Finishing',          'code' => 'FIN', 'estimated_duration_minutes' => 60],
            ['name' => 'Quality Control',   'code' => 'QC',  'estimated_duration_minutes' => 30],
            ['name' => 'Packaging',          'code' => 'PKG', 'estimated_duration_minutes' => 15],
        ],
        'furniture' => [
            ['name' => 'Wood Cutting',      'code' => 'CUT', 'estimated_duration_minutes' => 90],
            ['name' => 'Assembly',           'code' => 'ASM', 'estimated_duration_minutes' => 180],
            ['name' => 'Sanding',            'code' => 'SND', 'estimated_duration_minutes' => 60],
            ['name' => 'Painting/Finishing', 'code' => 'PNT', 'estimated_duration_minutes' => 120],
            ['name' => 'Quality Control',   'code' => 'QC',  'estimated_duration_minutes' => 30],
            ['name' => 'Packaging',          'code' => 'PKG', 'estimated_duration_minutes' => 30],
        ],
        'restaurant' => [
            ['name' => 'Preparation',       'code' => 'PRP', 'estimated_duration_minutes' => 30],
            ['name' => 'Cooking',            'code' => 'COK', 'estimated_duration_minutes' => 45],
            ['name' => 'Plating',            'code' => 'PLT', 'estimated_duration_minutes' => 10],
            ['name' => 'Quality Check',     'code' => 'QC',  'estimated_duration_minutes' => 5],
        ],
        'general' => [
            ['name' => 'Material Preparation', 'code' => 'PRP', 'estimated_duration_minutes' => 60],
            ['name' => 'Production',            'code' => 'PRD', 'estimated_duration_minutes' => 120],
            ['name' => 'Quality Control',      'code' => 'QC',  'estimated_duration_minutes' => 30],
            ['name' => 'Packaging',             'code' => 'PKG', 'estimated_duration_minutes' => 15],
        ],
    ];

    public function run(): void
    {
        $tenant = Tenant::query()->first();

        if (! $tenant) {
            return;
        }

        foreach ($this->stages as $industryType => $stages) {
            foreach ($stages as $index => $stage) {
                ProductionStage::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'industry_type' => $industryType,
                        'code' => $stage['code'],
                    ],
                    [
                        'name' => $stage['name'],
                        'sort_order' => ($index + 1) * 10,
                        'estimated_duration_minutes' => $stage['estimated_duration_minutes'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
