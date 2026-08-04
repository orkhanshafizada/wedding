<?php

namespace Modules\MainPage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Log\Traits\Auditable;

class MainPageSection extends Model
{
    use Auditable;

    protected $fillable = [
        'source_type',
        'source_reference',
        'menu_type',
        'menu_view_type',
        'limit',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'limit' => 'integer',
        'sort_order' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(MainPageSectionTranslation::class, 'main_page_section_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($builder) {
            $builder->where('status', 'Active')
                ->orWhere('status', 1)
                ->orWhere('status', true)
                ->orWhere('status', '1');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getTranslation(?int $languageId = null): ?MainPageSectionTranslation
    {
        if ($languageId !== null) {
            $translation = $this->translations->firstWhere('language_id', $languageId);
            if ($translation) {
                return $translation;
            }
        }

        return $this->translations->first();
    }
}
