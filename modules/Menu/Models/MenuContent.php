<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Log\Traits\Auditable;

class MenuContent extends Model
{
    use Auditable;

    protected $fillable = [
        'menu_id',
        'data',
        'main_photo',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function files()
    {
        return $this->hasMany(MenuContentFile::class, 'menu_content_id');
    }
}
