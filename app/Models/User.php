<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\AdminPermission\Models\AdminRole;
use Modules\Log\Traits\Auditable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, Auditable;

    protected $guarded = [];

    public function adminRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRole::class,
            'admin_role_user',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    public function syncAdminRoles(array $roleIds): void
    {
        $this->adminRoles()->sync($roleIds);
    }

    public function hasAdminRole(string $roleName): bool
    {
        return $this->adminRoles()
            ->where('admin_roles.name', $roleName)
            ->where('admin_roles.is_active', true)
            ->exists();
    }
}
