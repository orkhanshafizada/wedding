<?php

namespace Modules\Gallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Log\Traits\Auditable;

class GalleryAlbumItem extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'gallery_album_id',
        'type',
        'file_path',
        'video_url',
        'publication',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'publication' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(GalleryAlbumItemTranslation::class);
    }

    public function translation(string $locale = null): ?GalleryAlbumItemTranslation
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    public function getTitleAttribute(): string
    {
        return $this->translation()?->title ?? '';
    }

    public function getDescriptionAttribute(): string
    {
        return $this->translation()?->description ?? '';
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        return asset('storage/' . $this->file_path);
    }
}
