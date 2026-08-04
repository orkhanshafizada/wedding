<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Modules\Form\Models\FormLabel;
use Modules\Form\Models\FormText;
use Modules\Log\Traits\Auditable;
use Modules\Menu\Enums\MenuType;
use Modules\Product\Models\Filter\ProductFilter;

class Menu extends Model
{
    use Auditable;

    protected $fillable = [
        'uuid',
        'parent_id',
        'view_type',
        'status',
        'in_header',
        'in_footer',
        'show_on_main_page',
        'show_in_sitemap',
        'icon',
        'icon_image',
        'main_image',
        'text_color',
        'bg_color',
        'sort_order',
        'type',
    ];

    protected $casts = [
        'type' => MenuType::class,
        'status' => 'boolean',
        'in_header' => 'boolean',
        'in_footer' => 'boolean',
        'show_on_main_page' => 'boolean',
        'show_in_sitemap' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with(['childrenRecursive', 'translations']);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MenuTranslation::class);
    }

    public function content(): HasOne
    {
        return $this->hasOne(MenuContent::class, 'menu_id');
    }

    public function includedItems(): HasMany
    {
        return $this->hasMany(MenuIncludedItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function productFilters()
    {
        return $this->belongsToMany(ProductFilter::class, 'menu_product_filters', 'menu_id', 'product_filter_id')
            ->withPivot(['is_active', 'sort_order'])
            ->withTimestamps();
    }

    public function formText(): HasOne
    {
        return $this->hasOne(FormText::class, 'menu_id');
    }

    public function formLabels(): HasMany
    {
        return $this->hasMany(FormLabel::class, 'menu_id')->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeShownInSitemap(Builder $query): Builder
    {
        return $query->where('show_in_sitemap', true);
    }
}
