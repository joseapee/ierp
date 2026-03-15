<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small businesses just getting started.',
                'monthly_price' => 15000.00,
                'annual_price' => 150000.00,
                'trial_days' => 14,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'max_users' => '3',
                    'max_products' => '500',
                    'max_warehouses' => '1',
                    'manufacturing_enabled' => 'false',
                    'crm_enabled' => 'true',
                    'accounting_enabled' => 'true',
                    'procurement_enabled' => 'true',
                    'stock_enabled' => 'true',
                ],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For growing businesses that need more power.',
                'monthly_price' => 45000.00,
                'annual_price' => 450000.00,
                'trial_days' => 14,
                'is_active' => true,
                'sort_order' => 2,
                'features' => [
                    'max_users' => '10',
                    'max_products' => '5000',
                    'max_warehouses' => '3',
                    'manufacturing_enabled' => 'true',
                    'crm_enabled' => 'true',
                    'accounting_enabled' => 'true',
                    'procurement_enabled' => 'true',
                    'stock_enabled' => 'true',
                ],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For established businesses with advanced needs.',
                'monthly_price' => 95000.00,
                'annual_price' => 950000.00,
                'trial_days' => 14,
                'is_active' => true,
                'sort_order' => 3,
                'features' => [
                    'max_users' => 'unlimited',
                    'max_products' => 'unlimited',
                    'max_warehouses' => '10',
                    'manufacturing_enabled' => 'true',
                    'crm_enabled' => 'true',
                    'accounting_enabled' => 'true',
                    'procurement_enabled' => 'true',
                    'stock_enabled' => 'true',
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Full-featured plan for large organizations.',
                'monthly_price' => 195000.00,
                'annual_price' => 1950000.00,
                'trial_days' => 30,
                'is_active' => true,
                'sort_order' => 4,
                'features' => [
                    'max_users' => 'unlimited',
                    'max_products' => 'unlimited',
                    'max_warehouses' => 'unlimited',
                    'manufacturing_enabled' => 'true',
                    'crm_enabled' => 'true',
                    'accounting_enabled' => 'true',
                    'procurement_enabled' => 'true',
                    'stock_enabled' => 'true',
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = Plan::query()->updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            foreach ($features as $key => $value) {
                PlanFeature::query()->updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_key' => $key],
                    ['feature_value' => $value]
                );
            }
        }
    }
}
