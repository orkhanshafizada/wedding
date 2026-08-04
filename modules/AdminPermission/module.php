<?php

return [
    'version' => '1.0.0',
    'enabled' => true,
    'permission_group' => 'Users and Access',
    'auto_permissions' => false,
    'providers' => [
        Modules\AdminPermission\Providers\AdminPermissionServiceProvider::class,
    ],
];
