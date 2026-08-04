<?php

namespace Modules\Menu\Handlers\Web;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Menu\Contracts\MenuTypeWebHandler;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuContent;
use Symfony\Component\HttpFoundation\Response;

class ContentMenuWebHandler implements MenuTypeWebHandler
{
    /**
     * type = content olan menyular üçün web səhifə cavabını formalaşdırır.
     *
     * @param  Menu  $menu
     * @return Response|View
     */
    public function handle(Menu $menu): Response|View
    {
        $locale         = app()->getLocale();
        $fallbackLocale = config('app.locale');

        // Bu menyu üçün MenuContent qeydini tapırıq (yoxdursa yaradırıq)
        $page = MenuContent::firstOrCreate(
            ['menu_id' => $menu->id],
            ['data' => []]
        );

        $data = is_array($page->data) ? $page->data : [];

        /*
         * CONTENT MODULU (multilang)
         * $data strukturu:
         * [
         *   'az' => ['title' => '...', 'description' => '...'],
         *   'en' => ['title' => '...', 'description' => '...'],
         * ]
         */
        $contentData = $data[$locale]
            ?? ($fallbackLocale !== $locale ? ($data[$fallbackLocale] ?? null) : null)
            ?? (!empty($data) ? reset($data) : ['title' => '', 'description' => '']);

        $contentTitle = (string)($contentData['title'] ?? '');
        $contentBody  = (string)($contentData['description'] ?? '');

        /*
         * MENU TƏRCÜMƏLƏRİ (adı və SEO sahələri)
         *
         * Burada menunun öz tərcümə cədvəlindən götürürük:
         *  - name
         *  - meta_title
         *  - meta_description
         *  - meta_keywords
         */
        $translation = $menu->translations
            ->firstWhere('locale', $locale)
            ?? $menu->translations->firstWhere('locale', $fallbackLocale)
            ?? $menu->translations->first();

        $menuName            = $translation?->name ?? '';
        $menuMetaTitle       = $translation?->meta_title ?? null;
        $menuMetaDescription = $translation?->meta_description ?? null;
        $menuMetaKeywords    = $translation?->meta_keywords ?? null;

        /*
         * SƏHİFƏ TITLE (H1)
         * 1. Content modulunun title-i varsa → onu göstər
         * 2. Yoxdursa → menunun adını göstər
         */
        $pageTitle = $contentTitle !== '' ? $contentTitle : $menuName;

        /*
         * META TITLE
         * 1. menu.meta_title varsa → onu istifadə et
         * 2. Yoxdursa → pageTitle
         */
        $pageMetaTitle = $menuMetaTitle ?: ($pageTitle !== '' ? $pageTitle : null);

        /*
         * META DESCRIPTION
         * 1. menu.meta_description varsa → onu istifadə et
         * 2. Yoxdursa → content description-dan 160 simvolluq excerpt
         */
        if (!empty($menuMetaDescription)) {
            $pageMetaDescription = $menuMetaDescription;
        } elseif ($contentBody !== '') {
            $pageMetaDescription = Str::limit(strip_tags($contentBody), 160);
        } else {
            $pageMetaDescription = null;
        }

        $metaKeywords = $menuMetaKeywords ?: null;

        /*
         * ŞƏKİL URL
         * MenuContent::main_photo public disk-də saxlanılır:
         *  - menu/content/{menu_id}/file.jpg
         * URL üçün Storage::url istifadə edirik.
         */
        $contentImage = !empty($page->main_photo)
            ? Storage::disk('public')->url($page->main_photo)
            : null;

        return view('web.content.index', [
            // model & page
            'menu'  => $menu,
            'page'  => $page,

            // menu tərəfi
            'menuName'     => $menuName,
            'metaKeywords' => $metaKeywords,

            // content modulu tərəfi
            'contentTitle' => $contentTitle,
            'contentBody'  => $contentBody,
            'contentImage' => $contentImage,

            // final SEO & H1
            'pageTitle'           => $pageTitle,
            'pageMetaTitle'       => $pageMetaTitle,
            'pageMetaDescription' => $pageMetaDescription,
        ]);
    }
}
