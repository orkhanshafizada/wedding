<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Log\Traits\Auditable;

class Country extends Model
{
    use HasFactory, Auditable;

    protected $table = 'countries';

    protected $fillable = [
        'iso2',
        'iso3',
        'numcode',
        'un_member',
        'calling_code',
        'cctld',
        'short_names',
        'long_names',
        'is_active',
    ];

    protected $casts = [
        'short_names' => 'array',
        'long_names'  => 'array',
        'is_active'   => 'boolean',
    ];

    /**
     * Get localized short name.
     */
    public function getShortName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $names  = $this->short_names ?? [];

        return $names[$locale] ?? $names['en'] ?? null;
    }

    /**
     * Accessor: $country->short_name
     */
    public function getShortNameAttribute(): ?string
    {
        return $this->getShortName();
    }

    /**
     * Get localized long name.
     */
    public function getLongName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $names  = $this->long_names ?? [];

        return $names[$locale] ?? $names['en'] ?? null;
    }

    /**
     * Accessor: $country->long_name
     */
    public function getLongNameAttribute(): ?string
    {
        return $this->getLongName();
    }

    /**
     * Scope for active countries only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
