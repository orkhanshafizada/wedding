<?php

namespace Modules\LogosPartners\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Log\Traits\Auditable;
use Modules\Menu\Models\Menu;

class LogosPartner extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'logos_partners';

    protected $fillable = [
        'menu_id',
        'name',
        'description',
        'slug',
        'link',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'slug' => 'array',
        'link' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    // Accessors for multilanguage fields
    public function getNameAttribute($value): ?string
    {
        $names = json_decode($value, true);
        $locale = app()->getLocale();
        return $names[$locale] ?? $names['az'] ?? null;
    }

    public function getDescriptionAttribute($value): ?string
    {
        if (!$value) return null;
        $descriptions = json_decode($value, true);
        $locale = app()->getLocale();
        return $descriptions[$locale] ?? $descriptions['az'] ?? null;
    }

    public function getLinkAttribute($value): ?string
    {
        if (!$value) return null;
        $links = json_decode($value, true);
        $locale = app()->getLocale();
        return $links[$locale] ?? $links['az'] ?? null;
    }

    public function getSlugAttribute($value): ?string
    {
        if (!$value) return null;
        $links = json_decode($value, true);
        $locale = app()->getLocale();
        return $links[$locale] ?? $links['az'] ?? null;
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
