<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $put = function (string $group, string $key, $value) use ($now) {
            DB::table('settings')->updateOrInsert(
                ['group' => $group, 'key' => $key],
                [
                    'group'      => $group,
                    'key'        => $key,
                    'value'      => json_encode($value),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        };

        // ===== GENERAL =====
        $put('general', 'site_title', []);
        $put('general', 'site_header_text', []);
        $put('general', 'meta_title', []);
        $put('general', 'meta_description', []);
        $put('general', 'meta_keywords', []);
        $put('general', 'address', []);
        $put('general', 'map_iframe', '');
        $put('general', 'frontend_url', '');
        $put('general', 'email', 'info@example.com');
        $put('general', 'hr_email', 'hr@example.com');
        $put('general', 'phones', [
            [
                'is_whatsapp' => false,
                'label'       => 'Mobile',
                'number'      => '+994501234567',
            ],
        ]);
        $put('general', 'head_raw', '');
        $put('general', 'body_raw', '');
        $put('general', 'images', [
            'logo'             => null,
            'logo_dark'        => null,
            'footer_logo'      => null,
            'footer_logo_dark' => null,
            'mobile_logo'      => null,
            'mobile_logo_dark' => null,
            'favicon'          => null,
            'default_image'    => null,
            'watermark'        => null,
        ]);
        $put('general', 'work_hours', [
            'mon' => '09:00 - 18:00',
            'tue' => '09:00 - 18:00',
            'wed' => '09:00 - 18:00',
            'thu' => '09:00 - 18:00',
            'fri' => '09:00 - 18:00',
            'sat' => '09:00 - 18:00',
            'sun' => '09:00 - 18:00',
        ]);

        // ===== OG =====
        $put('og', 'title', []);
        $put('og', 'description', []);
        $put('og', 'type', 'website');
        $put('og', 'image', null);
        $put('og', 'canonical', '');
        $put('og', 'twitter_card', 'summary_large_image');
        $put('og', 'twitter_site', '');

        // ===== SOCIAL =====
        $put('social', 'facebook', [
            'link'   => '',
            'icon'   => 'fab fa-facebook',
            'active' => false,
        ]);

        $put('social', 'instagram', [
            'link'   => '',
            'icon'   => 'fab fa-instagram',
            'active' => false,
        ]);

        $put('social', 'twitter', [
            'link'   => '',
            'icon'   => 'fab fa-twitter',
            'active' => false,
        ]);

        $put('social', 'linkedin', [
            'link'   => '',
            'icon'   => 'fab fa-linkedin',
            'active' => false,
        ]);

        // ===== SMTP =====
        $put('smtp', 'driver', 'smtp');
        $put('smtp', 'host', '');
        $put('smtp', 'port', 465);
        $put('smtp', 'encryption', 'ssl');
        $put('smtp', 'username', '');
        $put('smtp', 'password', '');
        $put('smtp', 'from_email', '');
        $put('smtp', 'from_name', 'Laravel');

        // ===== SECURITY =====
        $put('security', 'max_login_attempts', 5);
        $put('security', 'lock_minutes', 15);
        $put('security', 'password_policy', [
            'min'    => 8,
            'upper'  => true,
            'digit'  => true,
            'symbol' => true,
        ]);
        $put('security', 'rate_limit', [
            'enabled'    => true,
            'max'        => 60,
            'window_min' => 1,
        ]);
        $put('security', 'captcha', [
            'enabled'    => false,
            'site_key'   => '',
            'secret_key' => '',
        ]);

        // ===== SEO =====
        $put('seo', 'google', []);
        $put('seo', 'yandex', []);
        $put('seo', 'facebook', []);
        $put('seo', 'sitemap', []);
        $put('seo', 'robots', '');

        // ===== OAUTH =====
        $put('oauth', 'telegram', []);
        $put('oauth', 'google', []);
        $put('oauth', 'facebook', []);
        $put('oauth', 'linkedin', []);

        // ===== SYSTEM =====
        $put('system', 'env', 'development');
        $put('system', 'timezone', 'Asia/Baku');
        $put('system', 'date_format', 'Y-m-d');
        $put('system', 'default_language_id', 1);
        $put('system', 'cache', [
            'enabled' => true,
            'driver'  => 'file',
            'ttl'     => '1 hour',
            'prefix'  => '',
        ]);
        $put('system', 'queue', [
            'driver'     => 'sync',
            'failed_ttl' => 30,
        ]);
        $put('system', 'backup', [
            'frequency'      => 'daily',
            'retention_days' => 7,
        ]);

        // ===== FILE MANAGER =====
        $put('file_manager', 'max_file_size', '10MB');
        $put('file_manager', 'max_image_size', '10MB');
        $put('file_manager', 'max_video_size', '20MB');
        $put('file_manager', 'image_quality', 85);
        $put('file_manager', 'storage_driver', 'local');
        $put('file_manager', 'allowed_images', [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        ]);
        $put('file_manager', 'allowed_videos', [
            'mp4', 'avi',
        ]);
        $put('file_manager', 'allowed_files', [
            'pdf', 'doc', 'docx', 'xls', 'xlsx',
        ]);
        $put('file_manager', 'sizes', [
            'thumb'  => ['w' => 150, 'h' => 150],
            'medium' => ['w' => 300, 'h' => 300],
            'large'  => ['w' => 800, 'h' => 800],
        ]);
        $put('file_manager', 'watermark', [
            'enabled'  => false,
            'position' => 'bottom-right',
            'opacity'  => 50,
        ]);
    }
}
