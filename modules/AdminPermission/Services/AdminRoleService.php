<?php

namespace Modules\AdminPermission\Services;

use Illuminate\Support\Facades\DB;
use Modules\AdminPermission\Models\AdminRole;

class AdminRoleService
{
    public function create(array $data): AdminRole
    {
        return DB::transaction(function () use ($data): AdminRole {
            $role = AdminRole::query()->create([
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? null,
                'is_system' => false,
                'is_super_admin' => (bool) ($data['is_super_admin'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);

            return $role->load('permissions');
        });
    }

    public function update(AdminRole $role, array $data): AdminRole
    {
        return DB::transaction(function () use ($role, $data): AdminRole {
            $role->update([
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? null,
                'is_super_admin' => (bool) ($data['is_super_admin'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);

            return $role->load('permissions');
        });
    }

    public function delete(AdminRole $role): void
    {
        DB::transaction(function () use ($role): void {
            $role->permissions()->detach();
            $role->users()->detach();
            $role->delete();
        });
    }
}
