<?php

namespace Modules\AdminPermission\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPermission\Models\AdminPermission;
use Modules\AdminPermission\Models\AdminRole;

class ModulePermissionSyncService
{
    public function sync(): int
    {
        if (! Schema::hasTable('admin_permissions') || ! Schema::hasTable('admin_roles')) {
            return 0;
        }

        $syncedCount = 0;

        DB::transaction(function () use (&$syncedCount): void {
            $this->deleteInvalidGeneratedPermissions();

            $adminRole = AdminRole::query()->firstOrCreate(
                [
                    'name' => 'admin',
                ],
                [
                    'display_name' => 'Administrator',
                    'is_system' => true,
                    'is_super_admin' => true,
                    'is_active' => true,
                ]
            );

            foreach ($this->discoverModules() as $module) {
                $permissions = $this->permissionsForModule($module);

                if ($permissions === []) {
                    continue;
                }

                $permissionIds = [];

                foreach ($permissions as $permission) {
                    $model = AdminPermission::query()->updateOrCreate(
                        [
                            'name' => $permission['name'],
                        ],
                        [
                            'display_name' => $permission['display_name'],
                            'group' => $permission['group'],
                            'scope' => $permission['scope'],
                            'module' => $permission['module'],
                            'action' => $permission['action'],
                            'menu_id' => null,
                            'sort_order' => $permission['sort_order'],
                            'is_active' => true,
                        ]
                    );

                    $permissionIds[] = (int) $model->id;
                    $syncedCount++;
                }

                $adminRole->permissions()->syncWithoutDetaching($permissionIds);
            }
        });

        return $syncedCount;
    }

    private function discoverModules(): array
    {
        $modules = [];
        $basePath = (string) config('modules.path', base_path('modules'));

        if (! File::isDirectory($basePath) && File::isDirectory(base_path('Modules'))) {
            $basePath = base_path('Modules');
        }

        if (! File::isDirectory($basePath)) {
            return $modules;
        }

        $disabledModules = collect((array) config('modules.disabled', []));

        foreach (File::directories($basePath) as $directory) {
            $moduleFile = $directory . DIRECTORY_SEPARATOR . 'module.php';

            if (! File::exists($moduleFile)) {
                continue;
            }

            $metadata = require $moduleFile;

            if (! is_array($metadata)) {
                continue;
            }

            $directoryName = basename($directory);
            $moduleName = Arr::get($metadata, 'name');

            if (! is_string($moduleName) || trim($moduleName) === '') {
                $moduleName = $directoryName;
            }

            $moduleName = trim($moduleName);
            $enabled = (bool) Arr::get($metadata, 'enabled', true);

            if ($disabledModules->contains($moduleName) || $disabledModules->contains($directoryName) || ! $enabled) {
                continue;
            }

            $permissionKey = Arr::get($metadata, 'permission_key');

            if (! is_string($permissionKey) || trim($permissionKey) === '') {
                $permissionKey = Str::snake($directoryName);
            }

            $permissionGroup = Arr::get($metadata, 'permission_group');

            if (! is_string($permissionGroup) || trim($permissionGroup) === '') {
                $permissionGroup = Str::headline($directoryName);
            }

            $permissions = Arr::get($metadata, 'permissions', []);

            if (! is_array($permissions)) {
                $permissions = [];
            }

            $modules[] = [
                'name' => $moduleName,
                'directory_name' => $directoryName,
                'path' => $directory,
                'permission_key' => trim($permissionKey),
                'permission_group' => trim($permissionGroup),
                'auto_permissions' => (bool) Arr::get($metadata, 'auto_permissions', true),
                'permissions' => $permissions,
            ];
        }

        return $modules;
    }

    private function permissionsForModule(array $module): array
    {
        $rawPermissions = [];

        if ($module['permissions'] !== []) {
            $rawPermissions = array_merge($rawPermissions, $module['permissions']);
        }

        $manifestPermissions = $this->permissionsFromModuleManifests(
            moduleName: $module['directory_name'],
            modulePath: $module['path']
        );

        if ($manifestPermissions !== []) {
            $rawPermissions = array_merge($rawPermissions, $manifestPermissions);
        }

        if ($rawPermissions === [] && $this->shouldCreateDefaultPermissions($module)) {
            $rawPermissions = $this->defaultModulePermissions($module['permission_key']);
        }

        return $this->normalizePermissionDefinitions(
            permissions: $rawPermissions,
            moduleKey: $module['permission_key'],
            fallbackGroup: $module['permission_group']
        );
    }

    private function shouldCreateDefaultPermissions(array $module): bool
    {
        if (! (bool) $module['auto_permissions']) {
            return false;
        }

        return ! in_array($module['permission_key'], $this->reservedPermissionKeys(), true);
    }

    private function reservedPermissionKeys(): array
    {
        return [
            'admin_permission',
        ];
    }

    private function defaultModulePermissions(string $moduleKey): array
    {
        return [
            $moduleKey . '.view',
            $moduleKey . '.create',
            $moduleKey . '.edit',
            $moduleKey . '.delete',
        ];
    }

    private function normalizePermissionDefinitions(array $permissions, string $moduleKey, string $fallbackGroup): array
    {
        $normalized = [];

        foreach ($permissions as $index => $permission) {
            if (is_string($permission)) {
                $name = trim($permission);

                if ($name === '') {
                    continue;
                }

                $action = $this->actionFromPermissionName($name);
                $module = $this->moduleFromPermissionName($name, $moduleKey);
                $group = $fallbackGroup;

                $normalized[$name] = [
                    'name' => $name,
                    'display_name' => $this->displayNameFromGroupAndAction($group, $action),
                    'group' => $group,
                    'scope' => 'system',
                    'module' => $module,
                    'action' => $action,
                    'sort_order' => ($index + 1) * 10,
                ];

                continue;
            }

            if (! is_array($permission)) {
                continue;
            }

            $name = trim((string) ($permission['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $action = trim((string) ($permission['action'] ?? $this->actionFromPermissionName($name)));
            $module = trim((string) ($permission['module'] ?? $this->moduleFromPermissionName($name, $moduleKey)));
            $group = trim((string) ($permission['group'] ?? $fallbackGroup));

            if ($action === '') {
                $action = 'view';
            }

            if ($module === '') {
                $module = $moduleKey;
            }

            if ($group === '') {
                $group = $fallbackGroup;
            }

            $normalized[$name] = [
                'name' => $name,
                'display_name' => trim((string) ($permission['display_name'] ?? $this->displayNameFromGroupAndAction($group, $action))),
                'group' => $group,
                'scope' => trim((string) ($permission['scope'] ?? 'system')),
                'module' => $module,
                'action' => $action,
                'sort_order' => (int) ($permission['sort_order'] ?? (($index + 1) * 10)),
            ];
        }

        return array_values($normalized);
    }

    private function actionFromPermissionName(string $name): string
    {
        $action = trim((string) Str::afterLast($name, '.'));

        return $action !== '' && $action !== $name ? $action : 'view';
    }

    private function moduleFromPermissionName(string $name, string $fallbackModule): string
    {
        $module = trim((string) Str::beforeLast($name, '.'));

        return $module !== '' && $module !== $name ? $module : $fallbackModule;
    }

    private function displayNameFromGroupAndAction(string $group, string $action): string
    {
        return Str::headline($group) . ' - ' . Str::headline($action);
    }

    private function permissionsFromModuleManifests(string $moduleName, string $modulePath): array
    {
        $seedersDirectory = $this->firstExistingDir([
            $modulePath . '/database/Seeders',
            $modulePath . '/database/seeders',
            $modulePath . '/Database/Seeders',
            $modulePath . '/Database/seeders',
        ]);

        if ($seedersDirectory === null) {
            return [];
        }

        $namespace = 'Modules\\' . $moduleName . '\\Database\\Seeders\\';
        $permissions = [];

        foreach (File::files($seedersDirectory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = $namespace . $file->getFilenameWithoutExtension();

            if (! class_exists($class)) {
                require_once $file->getPathname();
            }

            if (! class_exists($class)) {
                continue;
            }

            if (method_exists($class, 'permissionRows')) {
                $result = $class::permissionRows();

                if (is_array($result)) {
                    $permissions = array_merge($permissions, $result);
                }

                continue;
            }

            if (method_exists($class, 'permissions')) {
                $result = $class::permissions();

                if (is_array($result)) {
                    $permissions = array_merge($permissions, $result);
                }
            }
        }

        return $permissions;
    }

    private function deleteInvalidGeneratedPermissions(): void
    {
        AdminPermission::query()
            ->where('scope', 'system')
            ->whereIn('name', [
                'admin_permission.view',
                'admin_permission.create',
                'admin_permission.edit',
                'admin_permission.delete',
            ])
            ->delete();
    }

    private function firstExistingDir(array $candidates): ?string
    {
        foreach ($candidates as $directory) {
            if (File::isDirectory($directory)) {
                return $directory;
            }
        }

        return null;
    }
}
