<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Log\Traits\Auditable;

class MenuTranslation extends Model
{
    use Auditable;

    protected $fillable = [
        'menu_id',
        'locale',
        'name',
        'title',
        'description',
        'link',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
