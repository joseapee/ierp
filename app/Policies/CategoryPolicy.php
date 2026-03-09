<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.edit');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.delete');
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.edit');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.delete');
    }
}
