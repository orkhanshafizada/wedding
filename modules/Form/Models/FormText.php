<?php

namespace Modules\Form\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Log\Traits\Auditable;
use Modules\Menu\Models\Menu;

class FormText extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'menu_id',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FormTextTranslation::class);
    }
}
