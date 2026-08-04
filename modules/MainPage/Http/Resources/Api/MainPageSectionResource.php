<?php

namespace Modules\MainPage\Http\Resources\Api;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\MainPage\Enums\MainPageSectionSourceType;

class MainPageSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $languageId = Language::query()
            ->where('code', app()->getLocale())
            ->value('id');

        $translation = $this->translations->firstWhere('language_id', (int) $languageId)
            ?? $this->translations->first();

        return [
            'id' => (int) $this->id,
            'title' => (string) ($translation?->title ?? $this->defaultTitle()),
            'source_type' => (string) $this->source_type,
            'source_label' => $this->sourceLabel(),
            'source_reference' => $this->source_reference,
            'menu_type' => $this->menu_type,
            'menu_view_type' => $this->menu_view_type,
            'limit' => $this->limit !== null ? (int) $this->limit : null,
            'sort_order' => (int) $this->sort_order,
            'status' => (string) $this->status,
            'data' => $this->resource->resolved_data ?? ['items' => []],
        ];
    }

    private function sourceLabel(): string
    {
        $sourceType = MainPageSectionSourceType::tryFrom((string) $this->source_type);

        return $sourceType?->label() ?? (string) $this->source_type;
    }

    private function defaultTitle(): string
    {
        $sourceType = MainPageSectionSourceType::tryFrom((string) $this->source_type);

        return $sourceType?->label() ?? (string) $this->source_type;
    }
}
