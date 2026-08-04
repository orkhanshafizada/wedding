<?php

namespace Modules\Menu\Handlers\Api;

use Illuminate\Support\Facades\Storage;
use Modules\Menu\Contracts\MenuTypeApiHandler;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuContent;
use Modules\Menu\Services\MenuSeoService;
use Modules\Menu\Support\LocalePicker;

class ContentMenuApiHandler implements MenuTypeApiHandler
{
    public function __construct(
        protected readonly MenuSeoService $menuSeoService
    ) {
    }

    public function handle(Menu $menu, MenuDetailContext $context): array
    {
        $page = MenuContent::query()
            ->with(['files' => function ($query) {
                $query->orderBy('sort_order')->orderBy('id');
            }])
            ->where('menu_id', $menu->id)
            ->first();

        $data = $page?->data ?? [];
        $contentData = LocalePicker::pickArray($data, $context->locale, $context->fallbackLocale, [
            'title' => '',
            'description' => '',
        ]);

        $title = (string) ($contentData['title'] ?? '');
        $description = (string) ($contentData['description'] ?? '');

        $mainPhotoUrl = null;
        if (!empty($page?->main_photo)) {
            $mainPhotoUrl = Storage::disk('public')->url($page->main_photo);
        }

        $files = [];
        if ($page && $page->relationLoaded('files')) {
            foreach ($page->files as $file) {
                $files[] = [
                    'id' => $file->id,
                    'path' => $file->path,
                    'url' => Storage::disk('public')->url($file->path),
                    'original_name' => $file->original_name,
                    'extension' => $file->extension,
                    'mime_type' => $file->mime_type,
                    'size' => $file->size,
                    'sort_order' => $file->sort_order,
                ];
            }
        }

        return [
            'title' => $title,
            'description' => $description,
            'main_photo' => $mainPhotoUrl,
            'files' => $files,
            'seo' => $this->menuSeoService->buildMenuSeo(
                menu: $menu,
                locale: $context->locale,
                overrides: [
                    'title' => $title,
                    'description' => $description,
                    'meta_title' => $menu->getAttribute('api_meta_title') ?: $title,
                    'meta_description' => $menu->getAttribute('api_meta_description') ?: $description,
                    'meta_keywords' => $menu->getAttribute('api_meta_keywords'),
                    'image' => $mainPhotoUrl,
                    'image_alt' => $title ?: $menu->getAttribute('api_name'),
                    'article_section' => $menu->getAttribute('api_name'),
                    'published_time' => $page?->created_at?->format(\DateTimeInterface::ATOM),
                    'modified_time' => $page?->updated_at?->format(\DateTimeInterface::ATOM),
                    'og_type' => 'article',
                    'structured_type' => 'Article',
                ]
            ),
        ];
    }
}
