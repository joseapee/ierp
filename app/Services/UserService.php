<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Paginated, searchable, filterable list of users.
     *
     * @param  array{search?: string, role?: string, status?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with(['roles', 'tenant'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->whereHas('roles', fn ($q) => $q->where('slug', $role)
            ))
            ->when(isset($filters['status']), fn ($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new user and assign roles.
     *
     * @param  array{name: string, email: string, password: string, phone?: string, is_active?: bool, role_ids?: int[]}  $data
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (! empty($data['role_ids'])) {
                $user->roles()->sync($data['role_ids']);
            }

            return $user->load('roles');
        });
    }

    /**
     * Update an existing user and sync roles.
     *
     * @param  array{name?: string, email?: string, password?: string, phone?: string, is_active?: bool, role_ids?: int[]}  $data
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $attributes = collect($data)->only(['name', 'email', 'phone', 'is_active'])->toArray();

            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            $user->update($attributes);

            if (array_key_exists('role_ids', $data)) {
                $user->roles()->sync($data['role_ids'] ?? []);
            }

            return $user->load('roles');
        });
    }

    /**
     * Delete a user.
     */
    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }
}
