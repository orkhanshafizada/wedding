<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Log\Traits\Auditable;

class MenuContentFile extends Model
{
    use Auditable;

    protected $fillable = [
        'menu_content_id',
        'path',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'sort_order',
    ];

    public function content()
    {
        return $this->belongsTo(MenuContent::class, 'menu_content_id');
    }
}
