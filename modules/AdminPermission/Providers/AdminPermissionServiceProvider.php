<?php

namespace Modules\AdminPermission\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\AdminPermission\Services\AdminAccessService;
use Modules\AdminPermission\Services\AdminRoleService;
use Modules\AdminPermission\Services\AdminUserService;
use Modules\AdminPermission\Services\MenuPermissionSyncService;
use Modules\AdminPermission\Services\SystemPermissionSyncService;
use Modules\Menu\Models\Menu;

class AdminPermissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdminAccessService::class);
        $this->app->singleton(MenuPermissionSyncService::class);
        $this->app->singleton(SystemPermissionSyncService::class);
        $this->app->singleton(AdminRoleService::class);
        $this->app->singleton(AdminUserService::class);
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability, mixed $arguments = null): ?bool {
            if (! $user) {
                return false;
            }

            return app(AdminAccessService::class)->can($user, $ability, $arguments);
        });

        Menu::saved(function (Menu $menu): void {
            app(MenuPermissionSyncService::class)->sync($menu);
        });
    }
}
