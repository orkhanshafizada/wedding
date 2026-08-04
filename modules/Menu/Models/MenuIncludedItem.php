<?php

namespace Modules\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Menu\Enums\MenuIncludedItemType;
use Modules\Product\Models\Filter\ProductFilter;
use Modules\Slider\Models\Slider;

class MenuIncludedItem extends Model
{
    protected $fillable = [
        'menu_id',
        'included_type',
        'included_id',
        'sort_order',
    ];

    protected $casts = [
        'included_type' => MenuIncludedItemType::class,
        'included_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function includedMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'included_id');
    }

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class, 'included_id');
    }

    public function brandFilter(): BelongsTo
    {
        return $this->belongsTo(ProductFilter::class, 'included_id');
    }

    public function isMenu(): bool
    {
        return $this->included_type === MenuIncludedItemType::MENU;
    }

    public function isSlider(): bool
    {
        return $this->included_type === MenuIncludedItemType::SLIDER;
    }

    public function isBrand(): bool
    {
        return $this->included_type === MenuIncludedItemType::BRAND;
    }
}
