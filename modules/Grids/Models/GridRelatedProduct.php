<?php

namespace Modules\Grids\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Log\Traits\Auditable;
use Modules\Product\Models\Product;
use Modules\Product\Models\Variation\ProductVariation;

class GridRelatedProduct extends Model
{
    use Auditable;

    protected $table = 'grids_related_products';

    protected $fillable = [
        'grid_id',
        'product_id',
        'product_variation_id',
        'sort_order',
    ];

    protected $casts = [
        'grid_id' => 'integer',
        'product_id' => 'integer',
        'product_variation_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function grid(): BelongsTo
    {
        return $this->belongsTo(Grid::class, 'grid_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }
}
