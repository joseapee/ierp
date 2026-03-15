<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CrmPipelineStage;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CrmPipelineStageSeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    protected array $stages = [
        ['name' => 'Qualification', 'display_order' => 10, 'win_probability' => 10, 'color' => '#6c757d'],
        ['name' => 'Needs Analysis', 'display_order' => 20, 'win_probability' => 25, 'color' => '#17a2b8'],
        ['name' => 'Proposal', 'display_order' => 30, 'win_probability' => 50, 'color' => '#007bff'],
        ['name' => 'Negotiation', 'display_order' => 40, 'win_probability' => 75, 'color' => '#ffc107'],
        ['name' => 'Closed Won', 'display_order' => 50, 'win_probability' => 100, 'color' => '#28a745', 'is_won' => true],
        ['name' => 'Closed Lost', 'display_order' => 60, 'win_probability' => 0, 'color' => '#dc3545', 'is_lost' => true],
    ];

    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            foreach ($this->stages as $stage) {
                CrmPipelineStage::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $stage['name']],
                    array_merge([
                        'tenant_id' => $tenant->id,
                        'is_won' => false,
                        'is_lost' => false,
                        'is_active' => true,
                    ], $stage)
                );
            }
        }
    }
}
