<?php

namespace Modules\AdminPermission\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Log\Traits\Auditable;
use Modules\Menu\Models\Menu;

class AdminPermission extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'display_name',
        'group',
        'scope',
        'module',
        'action',
        'menu_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'menu_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRole::class,
            'admin_permission_role',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('scope', 'system');
    }

    public function scopeMenuScope(Builder $query): Builder
    {
        return $query->where('scope', 'menu');
    }
}
