<?php

namespace Modules\Form\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Log\Traits\Auditable;
use Modules\Menu\Models\Menu;

class FormResponse extends Model
{
    use HasFactory;
    use Auditable;

    public const STATUS_INACTIVE = 0;

    public const STATUS_ACTIVE = 1;

    protected $fillable = [
        'menu_id',
        'labels_data',
        'status',
    ];

    protected $casts = [
        'labels_data' => 'array',
        'status' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}