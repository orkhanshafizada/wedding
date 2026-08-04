<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\AdminPermission\Models\AdminPermission;
use Modules\AdminPermission\Models\AdminRole;
use Modules\AdminPermission\Services\MenuPermissionSyncService;
use Modules\AdminPermission\Services\ModulePermissionSyncService;
use Modules\AdminPermission\Services\SystemPermissionSyncService;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->syncPermissions();

        $role = $this->createAdminRole();

        $this->syncRolePermissions($role);

        $admins = [
            [
                'email' => 'admin@app.com',
                'fullname' => 'Administrator',
                'password' => 'secret',
            ],
            [
                'email' => 'admin@panel.com',
                'fullname' => 'OWN Administrator',
                'password' => 'P@nel2@26!',
                'is_secret' => true,
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = $this->createAdminUser($adminData);

            $admin->adminRoles()->sync([$role->id]);
        }
    }

    private function syncPermissions(): void
    {
        app(SystemPermissionSyncService::class)->sync();
        app(ModulePermissionSyncService::class)->sync();
        app(MenuPermissionSyncService::class)->syncAll();
    }

    private function createAdminRole(): AdminRole
    {
        return AdminRole::query()->updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'is_system' => true,
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );
    }

    private function syncRolePermissions(AdminRole $role): void
    {
        $permissionIds = AdminPermission::query()
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $role->permissions()->sync($permissionIds);
    }

    private function createAdminUser(array $data): User
    {
        return User::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'fullname' => $data['fullname'],
                'password' => Hash::make($data['password']),
                'status' => UserStatusEnum::Active,
                'is_secret' => $data['is_secret'] ?? false,
            ]
        );
    }
}
