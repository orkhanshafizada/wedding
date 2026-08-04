<?php

namespace Modules\Product\Http\Resources\Api\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VariationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->toPlainArray();

        $path = (string) ($data['main_image_path'] ?? $data['main_image'] ?? '');
        $mainImageUrl = null;

        if ($path !== '') {
            $mainImageUrl = preg_match('/^https?:\/\//i', $path)
                ? $path
                : Storage::disk('public')->url($path);
        }

        $gallery = $data['gallery'] ?? null;
        $filters = $data['filters'] ?? null;

        $uuid = $data['variation_uuid']
            ?? $data['uuid']
            ?? null;

        return [
            'id' => (int) ($data['variation_id'] ?? $data['id'] ?? 0),
            'uuid' => $uuid,

            'name' => (string) ($data['name'] ?? ''),
            'slug' => (string) ($data['slug'] ?? ''),

            'sku' => (string) ($data['sku'] ?? ''),
            'model' => (string) ($data['model'] ?? ''),

            'stock' => (int) ($data['stock'] ?? 0),

            'price' => (float) ($data['price'] ?? 0),
            'old_price' => array_key_exists('old_price', $data) && $data['old_price'] !== null
                ? (float) $data['old_price']
                : null,
            'discount_price' => array_key_exists('discount_price', $data) && $data['discount_price'] !== null
                ? (float) $data['discount_price']
                : null,

            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,

            'main_image' => $mainImageUrl,

            'gallery' => is_array($gallery) ? $gallery : [],
            'filters' => is_array($filters) ? $filters : [],
        ];
    }

    private function toPlainArray(): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        if (is_object($this->resource)) {
            return get_object_vars($this->resource);
        }

        return [];
    }
}
