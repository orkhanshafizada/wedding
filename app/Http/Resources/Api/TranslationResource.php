<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'key' => (string) $this->key,
            'locale' => (string) $this->locale,
            'value' => $this->value !== null ? (string) $this->value : null,
            'status' => $this->status !== null ? (string) $this->status : null,
            'updated_at' => $this->updated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
