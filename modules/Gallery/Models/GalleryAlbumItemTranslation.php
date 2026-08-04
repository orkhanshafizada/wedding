<?php

namespace Modules\Gallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Log\Traits\Auditable;

class GalleryAlbumItemTranslation extends Model
{
    use Auditable;

    public $timestamps = false;

    protected $fillable = [
        'gallery_album_item_id',
        'locale',
        'title',
        'description',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbumItem::class, 'gallery_album_item_id');
    }
}
