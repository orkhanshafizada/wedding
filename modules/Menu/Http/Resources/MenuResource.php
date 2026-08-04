<?php

namespace Modules\Menu\Http\Resources;

use App\Models\Language;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Menu\Models\MenuTranslation;
use Modules\Menu\Services\MenuSeoService;

class MenuResource extends JsonResource
{
    public function __construct($resource, protected bool $includeIncludedItems = false, protected bool $includeChildrenMenus = true)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $iconImagePath = $this->icon_image ? (string) $this->icon_image : null;
        $mainImagePath = $this->main_image ? (string) $this->main_image : null;

        $typeValue = is_object($this->type) && property_exists($this->type, 'value')
            ? (string) $this->type->value
            : (string) $this->type;

        $typeValue = trim($typeValue);

        $viewType = $this->view_type !== null ? trim((string) $this->view_type) : '';
        if ($viewType === '') {
            $viewType = 'default';
        }

        $filters = null;
        if ($typeValue === 'categories') {
            $filters = [
                'main_category_id' => (int) $this->id,
            ];
        }

        $locale = trim((string) ($request->query('locale', app()->getLocale()) ?? app()->getLocale()));
        if ($locale === '') {
            $locale = app()->getLocale();
        }

        $payload = [
            'id' => (int) $this->id,
            'uuid' => $this->uuid,
            'parent_id' => $this->parent_id ? (int) $this->parent_id : null,
            'type' => $typeValue,
            'view_type' => $viewType,
            'status' => (bool) $this->status,
            'in_header' => (bool) $this->in_header,
            'in_footer' => (bool) $this->in_footer,
            'show_on_main_page' => (bool) $this->show_on_main_page,
            'sort_order' => (int) $this->sort_order,
            'name' => (string) ($this->api_name ?? ''),
            'title' => $this->api_title ?? null,
            'description' => $this->api_description ?? null,
            'link' => $this->api_link ?? null,
            'multi_links' => $this->resolveMultiLinks(),
            'filters' => $filters,
            'icon' => [
                'text' => $this->icon ? (string) $this->icon : null,
                'image' => $iconImagePath,
                'image_url' => $iconImagePath ? Storage::disk('public')->url($iconImagePath) : null,
            ],
            'main_image' => [
                'path' => $mainImagePath,
                'url' => $mainImagePath ? Storage::disk('public')->url($mainImagePath) : null,
            ],
            'seo' => app(MenuSeoService::class)->buildMenuSeo($this->resource, $locale),
        ];

        if ($this->includeChildrenMenus) {
            $payload['children'] = $this->whenLoaded('childrenRecursive', function () use ($request): array {
                return $this->childrenRecursive
                    ->map(fn ($child) => (new self(
                        $child,
                        $this->includeIncludedItems,
                        $this->includeChildrenMenus
                    ))->resolve($request))
                    ->all();
            });
        }

        if ($this->includeIncludedItems) {
            $includedItems = $this->getAttribute('api_included_items');
            $payload['included_items'] = is_array($includedItems) ? $includedItems : [];
        }

        return $payload;
    }

    private function resolveMultiLinks(): array
    {
        $activeLanguageCodes = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->filter(fn ($code) => is_string($code) && trim($code) !== '')
            ->map(fn ($code) => trim((string) $code))
            ->values();

        if ($activeLanguageCodes->isEmpty()) {
            return [];
        }

        $translations = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        $translationMap = $translations
            ->filter(function (MenuTranslation $translation): bool {
                return is_string($translation->locale) && trim($translation->locale) !== '';
            })
            ->keyBy(function (MenuTranslation $translation): string {
                return trim((string) $translation->locale);
            });

        $multiLinks = [];

        foreach ($activeLanguageCodes as $languageCode) {
            $translation = $translationMap->get($languageCode);

            if (! $translation) {
                continue;
            }

            $link = trim((string) ($translation->link ?? ''));

            if ($link === '') {
                continue;
            }

            $multiLinks[$languageCode] = trim($link, '/');
        }

        return $multiLinks;
    }
}
