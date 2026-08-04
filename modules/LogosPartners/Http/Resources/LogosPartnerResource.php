<?php

namespace Modules\LogosPartners\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogosPartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $image = (string) ($this->image ?? '');
        $imageUrl = null;

        if ($image !== '') {
            $imageUrl = preg_match('/^https?:\/\//i', $image)
                ? $image
                : asset('storage/' . ltrim($image, '/'));
        }

        return [
            'id' => (int) $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'link' => $this->link,
            'image' => $imageUrl,
            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
