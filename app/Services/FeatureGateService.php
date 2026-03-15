<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;

class FeatureGateService
{
    /**
     * Check if a feature is enabled for the tenant's plan.
     */
    public function check(Tenant $tenant, string $featureKey): bool
    {
        $plan = $tenant->currentPlan;

        if (! $plan) {
            return false;
        }

        $value = $plan->getFeature($featureKey);

        if ($value === null) {
            return false;
        }

        if ($value === 'true' || $value === 'unlimited') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        return (int) $value > 0;
    }

    /**
     * Check if a usage limit has not been reached.
     */
    public function checkLimit(Tenant $tenant, string $featureKey, int $currentCount): bool
    {
        $limit = $this->getLimit($tenant, $featureKey);

        if ($limit === null) {
            return true;
        }

        return $currentCount < $limit;
    }

    /**
     * Get the numeric limit for a feature. Returns null for unlimited.
     */
    public function getLimit(Tenant $tenant, string $featureKey): ?int
    {
        return $tenant->getFeatureLimit($featureKey);
    }

    /**
     * Check if the tenant can create another user.
     */
    public function canCreateUser(Tenant $tenant): bool
    {
        return $this->checkLimit($tenant, 'max_users', $tenant->users()->count());
    }

    /**
     * Check if the tenant can create another product.
     */
    public function canCreateProduct(Tenant $tenant): bool
    {
        $count = $tenant->users()->first()
            ? \App\Models\Product::count()
            : 0;

        return $this->checkLimit($tenant, 'max_products', $count);
    }

    /**
     * Check if the tenant can create another warehouse.
     */
    public function canCreateWarehouse(Tenant $tenant): bool
    {
        $count = \App\Models\Warehouse::count();

        return $this->checkLimit($tenant, 'max_warehouses', $count);
    }

    /**
     * Check if a module is enabled for the tenant's plan.
     */
    public function isModuleEnabled(Tenant $tenant, string $module): bool
    {
        return $this->check($tenant, $module.'_enabled');
    }
}
