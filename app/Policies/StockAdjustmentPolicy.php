<?php

namespace App\Policies;

use App\Models\StockAdjustment;
use App\Models\User;

class StockAdjustmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('stock.view');
    }

    public function view(User $user, StockAdjustment $adjustment): bool
    {
        return $user->hasPermission('stock.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('stock.adjust');
    }

    public function approve(User $user, StockAdjustment $adjustment): bool
    {
        return $user->hasPermission('stock.approve-adjustments');
    }

    public function delete(User $user, StockAdjustment $adjustment): bool
    {
        return $user->hasPermission('stock.adjust');
    }
}
