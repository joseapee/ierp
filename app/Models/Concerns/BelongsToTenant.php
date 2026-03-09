<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;

/**
 * Applies tenant-level row isolation to any Eloquent model that uses it.
 *
 * Models using this trait will:
 * - Automatically scope queries to the current tenant.
 * - Automatically set tenant_id on new records when a current tenant is bound.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $model): void {
            if (app()->bound('current.tenant') && $model->tenant_id === null) {
                $model->tenant_id = app('current.tenant')->id;
            }
        });
    }
}
