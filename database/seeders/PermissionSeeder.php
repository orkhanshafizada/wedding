<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPermission\Models\AdminPermission;
use Modules\AdminPermission\Models\AdminRole;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('admin_permissions') || !Schema::hasTable('admin_roles')) {
            return;
        }

        DB::transaction(function (): void {
            $permissionIds = [];

            foreach ($this->permissionRows() as $index => $row) {
                $name = trim((string)($row['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $model = AdminPermission::query()->updateOrCreate(
                    [
                        'name' => $name,
                    ],
                    [
                        'display_name' => (string)($row['display_name'] ?? $this->displayName($name)),
                        'group'        => (string)($row['group'] ?? 'System'),
                        'scope'        => 'system',
                        'module'       => (string)($row['module'] ?? $this->moduleName($name)),
                        'action'       => (string)($row['action'] ?? $this->actionName($name)),
                        'menu_id'      => null,
                        'sort_order'   => (int)($row['sort_order'] ?? (($index + 1) * 10)),
                        'is_active'    => true,
                    ]
                );

                $permissionIds[] = (int)$model->id;
            }

            $adminRole = AdminRole::query()->firstOrCreate(
                [
                    'name' => 'admin',
                ],
                [
                    'display_name'   => 'Administrator',
                    'is_system'      => true,
                    'is_super_admin' => true,
                    'is_active'      => true,
                ]
            );

            $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        });
    }

    private function permissionRows(): array
    {
        return [
            ['name' => 'country.view', 'display_name' => 'Countries - View', 'group' => 'Settings Country', 'module' => 'country', 'action' => 'view', 'sort_order' => 10],
            ['name' => 'country.create', 'display_name' => 'Countries - Create', 'group' => 'Settings Country', 'module' => 'country', 'action' => 'create', 'sort_order' => 20],
            ['name' => 'country.edit', 'display_name' => 'Countries - Edit', 'group' => 'Settings Country', 'module' => 'country', 'action' => 'edit', 'sort_order' => 30],
            ['name' => 'country.delete', 'display_name' => 'Countries - Delete', 'group' => 'Settings Country', 'module' => 'country', 'action' => 'delete', 'sort_order' => 40],

            ['name' => 'settings.view', 'display_name' => 'Settings - View', 'group' => 'Settings', 'module' => 'settings', 'action' => 'view', 'sort_order' => 50],
            ['name' => 'settings.create', 'display_name' => 'Settings - Create', 'group' => 'Settings', 'module' => 'settings', 'action' => 'create', 'sort_order' => 60],
            ['name' => 'settings.edit', 'display_name' => 'Settings - Edit', 'group' => 'Settings', 'module' => 'settings', 'action' => 'edit', 'sort_order' => 70],
            ['name' => 'settings.delete', 'display_name' => 'Settings - Delete', 'group' => 'Settings', 'module' => 'settings', 'action' => 'delete', 'sort_order' => 80],

            ['name' => 'language.view', 'display_name' => 'Languages - View', 'group' => 'Settings Language', 'module' => 'language', 'action' => 'view', 'sort_order' => 90],
            ['name' => 'language.create', 'display_name' => 'Languages - Create', 'group' => 'Settings Language', 'module' => 'language', 'action' => 'create', 'sort_order' => 100],
            ['name' => 'language.edit', 'display_name' => 'Languages - Edit', 'group' => 'Settings Language', 'module' => 'language', 'action' => 'edit', 'sort_order' => 110],
            ['name' => 'language.delete', 'display_name' => 'Languages - Delete', 'group' => 'Settings Language', 'module' => 'language', 'action' => 'delete', 'sort_order' => 120],

            ['name' => 'translation.view', 'display_name' => 'Translations - View', 'group' => 'Settings Translation', 'module' => 'translation', 'action' => 'view', 'sort_order' => 130],
            ['name' => 'translation.create', 'display_name' => 'Translations - Create', 'group' => 'Settings Translation', 'module' => 'translation', 'action' => 'create', 'sort_order' => 140],
            ['name' => 'translation.edit', 'display_name' => 'Translations - Edit', 'group' => 'Settings Translation', 'module' => 'translation', 'action' => 'edit', 'sort_order' => 150],
            ['name' => 'translation.delete', 'display_name' => 'Translations - Delete', 'group' => 'Settings Translation', 'module' => 'translation', 'action' => 'delete', 'sort_order' => 160],

            ['name' => 'moderator.view', 'display_name' => 'Moderators - View', 'group' => 'Users and Access', 'module' => 'moderator', 'action' => 'view', 'sort_order' => 170],
            ['name' => 'moderator.create', 'display_name' => 'Moderators - Create', 'group' => 'Users and Access', 'module' => 'moderator', 'action' => 'create', 'sort_order' => 180],
            ['name' => 'moderator.edit', 'display_name' => 'Moderators - Edit', 'group' => 'Users and Access', 'module' => 'moderator', 'action' => 'edit', 'sort_order' => 190],
            ['name' => 'moderator.delete', 'display_name' => 'Moderators - Delete', 'group' => 'Users and Access', 'module' => 'moderator', 'action' => 'delete', 'sort_order' => 200],
        ];
    }

    private function moduleName(string $permissionName): string
    {
        $segments = explode('.', $permissionName);

        array_pop($segments);

        return implode('.', $segments) ?: $permissionName;
    }

    private function actionName(string $permissionName): string
    {
        $segments = explode('.', $permissionName);

        return (string)end($segments);
    }

    private function displayName(string $permissionName): string
    {
        return str($permissionName)
            ->replace('.', ' - ')
            ->headline()
            ->toString();
    }
}
