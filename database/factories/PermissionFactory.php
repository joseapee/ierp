<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $module = fake()->randomElement(['users', 'roles', 'tenants', 'dashboard']);
        $action = fake()->randomElement(['view', 'create', 'edit', 'delete']);

        return [
            'module' => $module,
            'action' => $action,
            'name' => ucfirst($action).' '.ucfirst($module),
            'slug' => $module.'.'.$action,
            'description' => "Can {$action} {$module}",
        ];
    }
}
