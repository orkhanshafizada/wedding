<?php

namespace Modules\Gallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Log\Traits\Auditable;
use Modules\Menu\Models\Menu;

class GalleryAlbum extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'menu_id',
        'show_album',
        'is_active',
        'cover_image',
        'sort_order',
    ];

    protected $casts = [
        'show_album' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(GalleryAlbumTranslation::class);
    }

    public function translation(string $locale = null): ?GalleryAlbumTranslation
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryAlbumItem::class)->orderBy('sort_order');
    }

    public function getNameAttribute(): string
    {
        return $this->translation()?->name ?? '';
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }
        return asset('storage/' . $this->cover_image);
    }
}
