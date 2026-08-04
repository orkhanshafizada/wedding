<?php

namespace Modules\Menu\Handlers\Web;

use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Modules\Menu\Contracts\MenuTypeWebHandler;
use Modules\Menu\Models\Menu;
use Symfony\Component\HttpFoundation\Response;

class ContactMenuWebHandler implements MenuTypeWebHandler
{
    /**
     * type = contact olan menyular üçün web səhifə cavabını formalaşdırır.
     *
     * @param  Menu  $menu
     * @return Response|View
     */
    public function handle(Menu $menu): Response|View
    {
        $locale         = app()->getLocale();
        $fallbackLocale = config('app.locale');

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
         * SAYT ÜMUMİ MƏLUMATLARI (Settings)
         *
         * - address: multilang ola bilər → locale/fallback qaydası ilə oxuyuruq
         * - site_about: meta description üçün fallback kimi istifadə oluna bilər
         * - email, phones, map_iframe, work_hours, social links və s.
         */

        // Address (multilang struktur ola bilər)
        $addressRaw = Settings::get('general', 'address', []);

        if (is_array($addressRaw)) {
            $contactAddress = $addressRaw[$locale]
                ?? ($fallbackLocale !== $locale ? ($addressRaw[$fallbackLocale] ?? null) : null)
                ?? (!empty($addressRaw) ? reset($addressRaw) : null);
        } else {
            $contactAddress = $addressRaw ?: null;
        }

        // Site about (multilang ola bilər) → meta üçün istifadə edəcəyik
        $siteAboutRaw = Settings::get('general', 'site_about', []);

        if (is_array($siteAboutRaw)) {
            $siteDescription = $siteAboutRaw[$locale]
                ?? ($fallbackLocale !== $locale ? ($siteAboutRaw[$fallbackLocale] ?? null) : null)
                ?? (!empty($siteAboutRaw) ? reset($siteAboutRaw) : null);
        } else {
            $siteDescription = $siteAboutRaw ?: null;
        }

        // Digər contact məlumatları
        $contactEmail = Settings::get('general', 'email', '');
        $phones       = Settings::get('general', 'phones', []);
        $mapIframe    = Settings::get('general', 'map_iframe', '');
        $workHours    = Settings::get('general', 'work_hours', []);

        // Telefonlar massivdirsə, birinci nömrəni "əsas" kimi götürə bilərik
        $primaryPhone = null;
        if (is_array($phones) && !empty($phones)) {
            $first        = $phones[0];
            $primaryPhone = is_array($first) ? ($first['number'] ?? null) : $first;
        }

        // Sosial şəbəkələr (əgər contact səhifəsində lazım olsa deyə ötürürük)
        $socialLinks = [
            'facebook'  => Settings::get('social', 'facebook',  ['link' => '', 'icon' => 'fab fa-facebook',  'active' => false]),
            'instagram' => Settings::get('social', 'instagram', ['link' => '', 'icon' => 'fab fa-instagram', 'active' => false]),
            'twitter'   => Settings::get('social', 'twitter',   ['link' => '', 'icon' => 'fab fa-twitter',   'active' => false]),
            'linkedin'  => Settings::get('social', 'linkedin',  ['link' => '', 'icon' => 'fab fa-linkedin',  'active' => false]),
        ];

        /*
         * SƏHİFƏ TITLE (H1)
         * 1. Menunun adı varsa → onu göstər
         * 2. Yoxdursa → null (Blade içində default dəyərdən istifadə oluna bilər)
         */
        $pageTitle = $menuName !== '' ? $menuName : null;

        /*
         * META TITLE
         * 1. menu.meta_title varsa → onu istifadə et
         * 2. Yoxdursa → pageTitle
         */
        $pageMetaTitle = $menuMetaTitle ?: ($pageTitle !== null ? $pageTitle : null);

        /*
         * META DESCRIPTION
         * 1. menu.meta_description varsa → onu istifadə et
         * 2. Yoxdursa → site_about-dan 160 simvolluq excerpt
         */
        if (!empty($menuMetaDescription)) {
            $pageMetaDescription = $menuMetaDescription;
        } elseif (!empty($siteDescription)) {
            $pageMetaDescription = Str::limit(strip_tags((string) $siteDescription), 160);
        } else {
            $pageMetaDescription = null;
        }

        $metaKeywords = $menuMetaKeywords ?: null;

        return view('web.contactus.index', [
            // Menu & SEO
            'menu'                => $menu,
            'menuName'            => $menuName,
            'pageTitle'           => $pageTitle,
            'pageMetaTitle'       => $pageMetaTitle,
            'pageMetaDescription' => $pageMetaDescription,
            'metaKeywords'        => $metaKeywords,

            // Sayt ümumi məlumatları
            'siteDescription' => $siteDescription,

            // Contact məlumatları
            'contactAddress' => $contactAddress,
            'contactEmail'   => $contactEmail,
            'contactPhones'  => $phones,
            'primaryPhone'   => $primaryPhone,
            'mapIframe'      => $mapIframe,
            'workHours'      => $workHours,
            'socialLinks'    => $socialLinks,
        ]);
    }
}
