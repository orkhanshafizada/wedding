<?php

namespace Modules\Grids\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GridRelatedVariationSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => (string) ($this['type'] ?? 'variation'),
            'id' => (int) ($this['id'] ?? 0),
            'product_id' => (int) ($this['product_id'] ?? 0),
            'text' => (string) ($this['text'] ?? ''),
            'subtitle' => (string) ($this['subtitle'] ?? ''),
            'image_url' => (string) ($this['image_url'] ?? ''),
        ];
    }
}
