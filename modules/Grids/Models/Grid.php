<?php

namespace Modules\Grids\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\Log\Traits\Auditable;
use Modules\Menu\Models\Menu;
use Modules\Product\Models\Product;

class Grid extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $fillable = [
        'menu_id',
        'datetime1',
        'datetime2',
        'banner',
        'name',
        'slug',
        'content',
        'location_or_group',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'datetime1' => 'datetime',
        'datetime2' => 'datetime',
        'name' => 'array',
        'slug' => 'array',
        'content' => 'array',
        'location_or_group' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'meta_keywords' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'banner_url',
        'main_image_url',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(GridMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'grids_related_products', 'grid_id', 'product_id')
            ->withPivot(['id', 'product_variation_id', 'sort_order'])
            ->withTimestamps()
            ->orderBy('grids_related_products.sort_order')
            ->orderBy('grids_related_products.id');
    }

    public function relatedProductItems(): HasMany
    {
        return $this->hasMany(GridRelatedProduct::class, 'grid_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function getBannerUrlAttribute(): ?string
    {
        $banner = trim((string) $this->banner);

        if ($banner === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $banner)) {
            return $banner;
        }

        return Storage::disk('public')->url($banner);
    }

    public function getMainImageUrlAttribute(): ?string
    {
        if ($this->relationLoaded('media')) {
            $mainMedia = $this->media
                ->first(fn ($media) => $media->type === 'image' && (bool) $media->is_main);
        } else {
            $mainMedia = $this->media()
                ->where('type', 'image')
                ->where('is_main', true)
                ->first();
        }

        if ($mainMedia) {
            return $mainMedia->url;
        }

        if ($this->relationLoaded('media')) {
            $fallbackMedia = $this->media
                ->first(fn ($media) => $media->type === 'image');
        } else {
            $fallbackMedia = $this->media()
                ->where('type', 'image')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();
        }

        return $fallbackMedia?->url;
    }
}
