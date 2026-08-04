<?php
namespace App\Services;

use App\Models\Language;
use App\Support\Settings;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class SettingService
{
    public function publicSettings(Language $language, Collection $activeLanguages): array
    {
        $generalSiteTitle = Settings::getLangMap('general', 'site_title', $activeLanguages, '');
        $generalSiteAbout = Settings::getLangMap('general', 'site_about', $activeLanguages, '');
        $generalSiteHeaderText = Settings::getLangMap('general', 'site_header_text', $activeLanguages, '');
        $generalAddress = Settings::getLangMap('general', 'address', $activeLanguages, '');

        $generalImages = (array) Settings::get('general', 'images', [
            'logo' => null,
            'logo_dark' => null,
            'footer_logo' => null,
            'footer_logo_dark' => null,
            'mobile_logo' => null,
            'mobile_logo_dark' => null,
            'favicon' => null,
            'wallpaper' => null,
            'watermark' => null,
            'placeholder' => null,
        ]);

        $generalImages = collect($generalImages)->map(function ($path) {
            if (!is_string($path) || $path === '') {
                return null;
            }

            return Str::startsWith($path, 'http')
                ? $path
                : asset('storage/' . ltrim($path, '/'));
        })->all();

        $ogTitle = Settings::getLangMap('og', 'title', $activeLanguages, '');
        $ogDescription = Settings::getLangMap('og', 'description', $activeLanguages, '');

        $ogImagePath = Settings::get('og', 'image', null);
        $ogImageUrl = '';
        if (is_string($ogImagePath) && $ogImagePath !== '') {
            $ogImageUrl = Str::startsWith($ogImagePath, 'http')
                ? $ogImagePath
                : asset('storage/' . ltrim($ogImagePath, '/'));
        }

        return [
            'language_id' => (int) $language->id,
            'general' => [
                'site_title' => (string) ($generalSiteTitle[$language->id] ?? ''),
                'site_about' => (string) ($generalSiteAbout[$language->id] ?? ''),
                'site_header_text' => (string) ($generalSiteHeaderText[$language->id] ?? ''),
                'address' => (string) ($generalAddress[$language->id] ?? ''),
                'map_iframe' => (string) Settings::get('general', 'map_iframe', ''),
                'frontend_url' => (string) Settings::get('general', 'frontend_url', ''),
                'email' => (string) Settings::get('general', 'email', ''),
                'phones' => array_values((array) Settings::get('general', 'phones', [])),
                'head_raw' => (string) Settings::get('general', 'head_raw', ''),
                'body_raw' => (string) Settings::get('general', 'body_raw', ''),
                'images' => $generalImages,
                'work_hours' => (array) Settings::get('general', 'work_hours', [
                    'mon' => ['day' => '09:00 - 18:00', 'eve' => '09:00 - 18:00'],
                    'tue' => ['day' => '09:00 - 18:00', 'eve' => '09:00 - 18:00'],
                    'wed' => ['day' => '09:00 - 18:00', 'eve' => '09:00 - 18:00'],
                    'thu' => ['day' => '09:00 - 18:00', 'eve' => '09:00 - 18:00'],
                    'fri' => ['day' => '09:00 - 18:00', 'eve' => '09:00 - 18:00'],
                    'sat' => ['day' => '09:00 - 18:00', 'eve' => '09:00 - 18:00'],
                    'sun' => ['day' => '09:00 - 18:00', 'eve' => '09:00 - 18:00'],
                ]),
                'square' => [
                    'price' => (float) Settings::get('square', 'price', 10),
                ],
            ],
            'og' => [
                'title' => (string) ($ogTitle[$language->id] ?? ''),
                'description' => (string) ($ogDescription[$language->id] ?? ''),
                'type' => (string) Settings::get('og', 'type', 'website'),
                'canonical' => (string) Settings::get('og', 'canonical', ''),
                'image' => $ogImageUrl,
                'twitter_card' => (string) Settings::get('og', 'twitter_card', 'summary_large_image'),
                'twitter_site' => (string) Settings::get('og', 'twitter_site', ''),
            ],
            'social' => [
                'facebook' => (array) Settings::get('social', 'facebook', ['link' => '', 'icon' => 'fab fa-facebook', 'active' => false]),
                'instagram' => (array) Settings::get('social', 'instagram', ['link' => '', 'icon' => 'fab fa-instagram', 'active' => false]),
                'twitter' => (array) Settings::get('social', 'twitter', ['link' => '', 'icon' => 'fab fa-twitter', 'active' => false]),
                'linkedin' => (array) Settings::get('social', 'linkedin', ['link' => '', 'icon' => 'fab fa-linkedin', 'active' => false]),
            ],
            'seo' => [
                'google' => (array) Settings::get('seo', 'google', []),
                'yandex' => (array) Settings::get('seo', 'yandex', []),
                'facebook' => (array) Settings::get('seo', 'facebook', []),
                'sitemap' => (array) Settings::get('seo', 'sitemap', []),
                'robots' => (string) Settings::get('seo', 'robots', ''),
            ],
        ];
    }
}
