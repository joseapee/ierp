<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\UnitConversion;
use App\Models\User;
use App\Models\Warehouse;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\StockAdjustmentPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UnitConversionPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Apple\AppleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Apple Sign In Socialite driver
        Event::listen(SocialiteWasCalled::class, AppleExtendSocialite::class);

        // Super admin bypasses ALL gate checks
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->is_super_admin) {
                return true;
            }

            return null;
        });

        // Dynamically register all seeded permissions as Laravel Gates
        // Guard: only register if the permissions table has been migrated
        if (Schema::hasTable('permissions')) {
            Permission::query()->each(function (Permission $permission): void {
                Gate::define($permission->slug, function (User $user) use ($permission): bool {
                    return $user->hasPermission($permission->slug);
                });
            });
        }

        // Register model policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(StockAdjustment::class, StockAdjustmentPolicy::class);
        Gate::policy(UnitConversion::class, UnitConversionPolicy::class);
    }
}
