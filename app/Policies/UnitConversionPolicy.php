<?php

namespace App\Policies;

use App\Models\UnitConversion;
use App\Models\User;

class UnitConversionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('units.view');
    }

    public function view(User $user, UnitConversion $unitConversion): bool
    {
        return $user->hasPermission('units.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('units.create');
    }

    public function update(User $user, UnitConversion $unitConversion): bool
    {
        return $user->hasPermission('units.edit');
    }

    public function delete(User $user, UnitConversion $unitConversion): bool
    {
        return $user->hasPermission('units.delete');
    }
}
