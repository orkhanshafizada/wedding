<?php

namespace Modules\AdminPermission\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\AdminPermission\Models\AdminPermission;
use Modules\Menu\Models\Menu;

class AdminAccessService
{
    public function can(User $user, string $ability, mixed $arguments = null): bool
    {
        if (! $user->exists) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $permissionNames = $this->resolvePermissionNames($ability, $arguments);

        if ($permissionNames === []) {
            return false;
        }

        $userPermissionNames = $this->permissionNames($user);

        foreach ($permissionNames as $permissionName) {
            if ($userPermissionNames->contains($permissionName)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->adminRoles()
            ->where('admin_roles.is_active', true)
            ->where('admin_roles.is_super_admin', true)
            ->exists();
    }

    public function permissionNames(User $user): Collection
    {
        return AdminPermission::query()
            ->select('admin_permissions.name')
            ->join('admin_permission_role', 'admin_permission_role.permission_id', '=', 'admin_permissions.id')
            ->join('admin_roles', 'admin_roles.id', '=', 'admin_permission_role.role_id')
            ->join('admin_role_user', 'admin_role_user.role_id', '=', 'admin_roles.id')
            ->where('admin_role_user.user_id', $user->id)
            ->where('admin_permissions.is_active', true)
            ->where('admin_roles.is_active', true)
            ->pluck('admin_permissions.name')
            ->unique()
            ->values();
    }

    public function hasAnyMenuPermission(User $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->permissionNames($user)->contains('menu.view')) {
            return true;
        }

        return AdminPermission::query()
            ->join('admin_permission_role', 'admin_permission_role.permission_id', '=', 'admin_permissions.id')
            ->join('admin_roles', 'admin_roles.id', '=', 'admin_permission_role.role_id')
            ->join('admin_role_user', 'admin_role_user.role_id', '=', 'admin_roles.id')
            ->where('admin_role_user.user_id', $user->id)
            ->where('admin_permissions.scope', 'menu')
            ->where('admin_permissions.is_active', true)
            ->where('admin_roles.is_active', true)
            ->exists();
    }

    public function allowedMenuIds(User $user, string $action): array
    {
        if ($this->isSuperAdmin($user)) {
            return $this->allMenuIds();
        }

        if ($this->permissionNames($user)->contains('menu.' . $action)) {
            return $this->allMenuIds();
        }

        return $this->scopedMenuIdsForActions($user, [$action]);
    }

    public function allowedMenuIdsForActions(User $user, array $actions): array
    {
        if ($this->isSuperAdmin($user)) {
            return $this->allMenuIds();
        }

        $actions = $this->normalizeActions($actions);

        if ($actions === []) {
            return [];
        }

        $permissionNames = $this->permissionNames($user);

        foreach ($actions as $action) {
            if ($permissionNames->contains('menu.' . $action)) {
                return $this->allMenuIds();
            }
        }

        return $this->scopedMenuIdsForActions($user, $actions);
    }

    public function scopedMenuIdsForActions(User $user, array $actions): array
    {
        if ($this->isSuperAdmin($user)) {
            return $this->allMenuIds();
        }

        $actions = $this->normalizeActions($actions);

        if ($actions === []) {
            return [];
        }

        return AdminPermission::query()
            ->join('admin_permission_role', 'admin_permission_role.permission_id', '=', 'admin_permissions.id')
            ->join('admin_roles', 'admin_roles.id', '=', 'admin_permission_role.role_id')
            ->join('admin_role_user', 'admin_role_user.role_id', '=', 'admin_roles.id')
            ->where('admin_role_user.user_id', $user->id)
            ->where('admin_permissions.scope', 'menu')
            ->whereIn('admin_permissions.action', $actions)
            ->whereNotNull('admin_permissions.menu_id')
            ->where('admin_permissions.is_active', true)
            ->where('admin_roles.is_active', true)
            ->pluck('admin_permissions.menu_id')
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function allowedMenuTypeValues(User $user, array $actions = ['view', 'content', 'edit', 'delete']): array
    {
        if ($this->isSuperAdmin($user)) {
            return Menu::query()
                ->select('type')
                ->distinct()
                ->pluck('type')
                ->map(fn ($type): string => $this->normalizeMenuTypeValue($type))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $permissionNames = $this->permissionNames($user);

        foreach ($actions as $action) {
            $action = trim((string) $action);

            if ($action !== '' && $permissionNames->contains('menu.' . $action)) {
                return Menu::query()
                    ->select('type')
                    ->distinct()
                    ->pluck('type')
                    ->map(fn ($type): string => $this->normalizeMenuTypeValue($type))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        $menuIds = $this->scopedMenuIdsForActions($user, $actions);

        if ($menuIds === []) {
            return [];
        }

        return Menu::query()
            ->whereIn('id', $menuIds)
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->map(fn ($type): string => $this->normalizeMenuTypeValue($type))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function canUseMenuType(User $user, string $type): bool
    {
        $type = trim($type);

        if ($type === '') {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return in_array($type, $this->allowedMenuTypeValues($user), true);
    }

    private function resolvePermissionNames(string $ability, mixed $arguments = null): array
    {
        $ability = trim($ability);

        if ($ability === '') {
            return [];
        }

        $argument = is_array($arguments) ? ($arguments[0] ?? null) : $arguments;

        if ($argument instanceof Menu && str_starts_with($ability, 'menu.')) {
            $action = str($ability)->after('menu.')->toString();

            return [
                'menu:' . $argument->id . '.' . $action,
                $ability,
            ];
        }

        if ($argument instanceof Model && str_starts_with($ability, 'menu.')) {
            $action = str($ability)->after('menu.')->toString();

            return [
                'menu:' . $argument->getKey() . '.' . $action,
                $ability,
            ];
        }

        return [$ability];
    }

    private function allMenuIds(): array
    {
        return Menu::query()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeActions(array $actions): array
    {
        return collect($actions)
            ->map(fn ($action): string => trim((string) $action))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeMenuTypeValue(mixed $type): string
    {
        if ($type instanceof \BackedEnum) {
            return trim((string) $type->value);
        }

        return trim((string) $type);
    }
}
