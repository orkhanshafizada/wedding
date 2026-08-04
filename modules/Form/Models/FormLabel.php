<?php

namespace Modules\Form\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Log\Traits\Auditable;
use Modules\Menu\Models\Menu;

class FormLabel extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'menu_id',
        'type',
        'is_required',
        'send_text_mail',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'send_text_mail' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FormLabelTranslation::class);
    }

    public function translation(?string $locale = null): ?FormLabelTranslation
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }
}
