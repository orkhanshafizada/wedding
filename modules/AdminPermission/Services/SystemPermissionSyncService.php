<?php

namespace Modules\AdminPermission\Services;

use Illuminate\Support\Facades\DB;
use Modules\AdminPermission\Models\AdminPermission;

class SystemPermissionSyncService
{
    public function sync(): int
    {
        $count = 0;

        DB::transaction(function () use (&$count): void {
            foreach ($this->permissions() as $permission) {
                AdminPermission::query()->updateOrCreate(
                    [
                        'name' => $permission['name'],
                    ],
                    [
                        'display_name' => $permission['display_name'],
                        'group' => $permission['group'],
                        'scope' => 'system',
                        'module' => $permission['module'],
                        'action' => $permission['action'],
                        'menu_id' => null,
                        'sort_order' => $permission['sort_order'],
                        'is_active' => true,
                    ]
                );

                $count++;
            }
        });

        return $count;
    }

    private function permissions(): array
    {
        return [
            ['name' => 'admin.view', 'display_name' => 'Admins - View', 'group' => 'Users and Access', 'module' => 'admin', 'action' => 'view', 'sort_order' => 10],
            ['name' => 'admin.create', 'display_name' => 'Admins - Create', 'group' => 'Users and Access', 'module' => 'admin', 'action' => 'create', 'sort_order' => 20],
            ['name' => 'admin.edit', 'display_name' => 'Admins - Edit', 'group' => 'Users and Access', 'module' => 'admin', 'action' => 'edit', 'sort_order' => 30],
            ['name' => 'admin.delete', 'display_name' => 'Admins - Delete', 'group' => 'Users and Access', 'module' => 'admin', 'action' => 'delete', 'sort_order' => 40],

            ['name' => 'role.view', 'display_name' => 'Roles - View', 'group' => 'Users and Access', 'module' => 'role', 'action' => 'view', 'sort_order' => 50],
            ['name' => 'role.create', 'display_name' => 'Roles - Create', 'group' => 'Users and Access', 'module' => 'role', 'action' => 'create', 'sort_order' => 60],
            ['name' => 'role.edit', 'display_name' => 'Roles - Edit', 'group' => 'Users and Access', 'module' => 'role', 'action' => 'edit', 'sort_order' => 70],
            ['name' => 'role.delete', 'display_name' => 'Roles - Delete', 'group' => 'Users and Access', 'module' => 'role', 'action' => 'delete', 'sort_order' => 80],

            ['name' => 'permission.view', 'display_name' => 'Permissions - View', 'group' => 'Users and Access', 'module' => 'permission', 'action' => 'view', 'sort_order' => 90],
            ['name' => 'permission.create', 'display_name' => 'Permissions - Create', 'group' => 'Users and Access', 'module' => 'permission', 'action' => 'create', 'sort_order' => 100],
            ['name' => 'permission.edit', 'display_name' => 'Permissions - Edit', 'group' => 'Users and Access', 'module' => 'permission', 'action' => 'edit', 'sort_order' => 110],
            ['name' => 'permission.delete', 'display_name' => 'Permissions - Delete', 'group' => 'Users and Access', 'module' => 'permission', 'action' => 'delete', 'sort_order' => 120],

            ['name' => 'menu.view', 'display_name' => 'Menus - View', 'group' => 'Menus', 'module' => 'menu', 'action' => 'view', 'sort_order' => 130],
            ['name' => 'menu.create', 'display_name' => 'Menus - Create', 'group' => 'Menus', 'module' => 'menu', 'action' => 'create', 'sort_order' => 140],
            ['name' => 'menu.edit', 'display_name' => 'Menus - Edit', 'group' => 'Menus', 'module' => 'menu', 'action' => 'edit', 'sort_order' => 150],
            ['name' => 'menu.delete', 'display_name' => 'Menus - Delete', 'group' => 'Menus', 'module' => 'menu', 'action' => 'delete', 'sort_order' => 160],

            ['name' => 'settings.view', 'display_name' => 'Settings - View', 'group' => 'Settings', 'module' => 'settings', 'action' => 'view', 'sort_order' => 170],
            ['name' => 'settings.edit', 'display_name' => 'Settings - Edit', 'group' => 'Settings', 'module' => 'settings', 'action' => 'edit', 'sort_order' => 180],
        ];
    }
}
