<?php

namespace Modules\Form\Handlers\Api;

use Modules\Form\Http\Resources\FormLabelResource;
use Modules\Form\Http\Resources\FormTextResource;
use Modules\Form\Models\FormLabel;
use Modules\Form\Models\FormText;
use Modules\Menu\Contracts\MenuTypeApiHandler;
use Modules\Menu\DTO\MenuDetailContext;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuSeoService;

class FormMenuApiHandler implements MenuTypeApiHandler
{
    public function __construct(
        protected readonly MenuSeoService $menuSeoService
    ) {
    }

    public function handle(Menu $menu, MenuDetailContext $context): array
    {
        $text = FormText::query()
            ->with('translations')
            ->where('menu_id', $menu->id)
            ->first();

        $labels = FormLabel::query()
            ->with('translations')
            ->where('menu_id', $menu->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $headerText = null;
        $successText = null;
        $emailText = null;

        if ($text) {
            $resolvedText = (new FormTextResource($text))->resolve();
            $headerText = $resolvedText['header_text'] ?? null;
            $successText = $resolvedText['success_text'] ?? null;
            $emailText = $resolvedText['email_text'] ?? null;
        } else {
            $resolvedText = [
                'header_text' => null,
                'success_text' => null,
                'email_text' => null,
            ];
        }

        return [
            'mode' => 'form',
            'menu_id' => (int) $menu->id,
            'menu_uuid' => (string) $menu->uuid,
            'text' => $resolvedText,
            'fields' => FormLabelResource::collection($labels)->resolve(),
            'submit' => [
                'method' => 'POST',
                'path' => '/forms/menus/' . $menu->uuid . '/responses',
                'route' => 'forms.menus.responses.store',
            ],
            'seo' => $this->menuSeoService->buildMenuSeo(
                menu: $menu,
                locale: $context->locale,
                overrides: [
                    'title' => $headerText ?: $menu->getAttribute('api_title'),
                    'description' => $successText ?: $emailText ?: $menu->getAttribute('api_description'),
                    'meta_title' => $menu->getAttribute('api_meta_title') ?: $headerText ?: $menu->getAttribute('api_title'),
                    'meta_description' => $menu->getAttribute('api_meta_description') ?: $successText ?: $emailText ?: $menu->getAttribute('api_description'),
                    'meta_keywords' => $menu->getAttribute('api_meta_keywords'),
                    'image' => null,
                    'image_alt' => $headerText ?: $menu->getAttribute('api_name'),
                    'article_section' => $menu->getAttribute('api_name'),
                    'published_time' => $text?->created_at?->format(\DateTimeInterface::ATOM),
                    'modified_time' => $text?->updated_at?->format(\DateTimeInterface::ATOM),
                    'og_type' => 'website',
                    'structured_type' => 'ContactPage',
                ]
            ),
        ];
    }
}
