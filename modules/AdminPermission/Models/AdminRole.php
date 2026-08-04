<?php

namespace Modules\AdminPermission\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Log\Traits\Auditable;

class AdminRole extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'display_name',
        'is_system',
        'is_super_admin',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminPermission::class,
            'admin_permission_role',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'admin_role_user',
            'role_id',
            'user_id'
        )->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
