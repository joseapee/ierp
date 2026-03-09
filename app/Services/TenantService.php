<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TenantService
{
    /**
     * Paginated, searchable tenant list (no tenant scope — super admin only).
     *
     * @param  array{search?: string, status?: string, plan?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Tenant::query()
            ->withCount('users')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('domain', 'like', "%{$search}%")
            ))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['plan'] ?? null, fn ($q, $plan) => $q->where('plan', $plan))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new tenant.
     *
     * @param  array{name: string, slug: string, domain?: string, plan?: string, settings?: array}  $data
     */
    public function create(array $data): Tenant
    {
        return Tenant::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'domain' => $data['domain'] ?? null,
            'plan' => $data['plan'] ?? 'starter',
            'settings' => $data['settings'] ?? [],
            'status' => 'active',
        ]);
    }

    /**
     * Update a tenant.
     *
     * @param  array{name?: string, slug?: string, domain?: string, plan?: string, settings?: array}  $data
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update(
            collect($data)->only(['name', 'slug', 'domain', 'plan', 'settings'])->toArray()
        );

        return $tenant->refresh();
    }

    /**
     * Suspend a tenant.
     */
    public function suspend(Tenant $tenant): void
    {
        $tenant->update(['status' => 'suspended']);
    }

    /**
     * Activate a tenant.
     */
    public function activate(Tenant $tenant): void
    {
        $tenant->update(['status' => 'active']);
    }
}
