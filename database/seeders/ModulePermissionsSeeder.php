<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AdminPermission\Services\ModulePermissionSyncService;

class ModulePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(ModulePermissionSyncService::class)->sync();
    }
}
