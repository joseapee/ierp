<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Permission;

class PermissionService
{
    /**
     * Return all permissions grouped by module.
     *
     * @return array<string, \Illuminate\Database\Eloquent\Collection<int, Permission>>
     */
    public function allGroupedByModule(): array
    {
        return Permission::query()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module')
            ->toArray();
    }
}
