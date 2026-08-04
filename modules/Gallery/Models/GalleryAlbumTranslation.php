<?php

namespace Modules\Gallery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Log\Traits\Auditable;

class GalleryAlbumTranslation extends Model
{
    use Auditable;

    public $timestamps = false;

    protected $fillable = [
        'gallery_album_id',
        'locale',
        'name',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }
}
