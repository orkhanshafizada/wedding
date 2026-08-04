<?php

namespace Modules\TeamStaff\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Log\Traits\Auditable;

class TeamStaff extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'team_staff';

    protected $fillable = [
        'menu_id',
        'name',
        'company',
        'position',
        'description',
        'phone',
        'mobile',
        'email',
        'profile_picture',
        'social_networks',
        'files',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'company' => 'array',
        'position' => 'array',
        'description' => 'array',
        'social_networks' => 'array',
        'files' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function menu()
    {
        return $this->belongsTo(\Modules\Menu\Models\Menu::class);
    }

    // Accessors for multilanguage fields
    public function getNameAttribute($value): ?string
    {
        $names = json_decode($value, true);
        $locale = app()->getLocale();
        return $names[$locale] ?? $names['az'] ?? null;
    }

    public function getCompanyAttribute($value): ?string
    {
        if (!$value) return null;
        $companies = json_decode($value, true);
        $locale = app()->getLocale();
        return $companies[$locale] ?? $companies['az'] ?? null;
    }

    public function getPositionAttribute($value): ?string
    {
        if (!$value) return null;
        $positions = json_decode($value, true);
        $locale = app()->getLocale();
        return $positions[$locale] ?? $positions['az'] ?? null;
    }

    public function getDescriptionAttribute($value): ?string
    {
        if (!$value) return null;
        $descriptions = json_decode($value, true);
        $locale = app()->getLocale();
        return $descriptions[$locale] ?? $descriptions['az'] ?? null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id', 'desc');
    }
}
