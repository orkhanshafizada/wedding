<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\UpdateSettingRequest;
use App\Models\Language;
use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Intervention\Image\ImageManagerStatic as Image;

class SettingController extends Controller
{
    public function edit()
    {
        $languages = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $requiredLanguages = $languages->where('is_required', true)->values();

        $data = [
            'languages'             => $languages,
            'required_languages'    => $requiredLanguages,
            'required_language_ids' => $requiredLanguages->pluck('id')->values(),

            // GENERAL
            'general'               => [
                'site_title'       => Settings::getLangMap('general', 'site_title', $languages, ''),
                'site_header_text' => Settings::getLangMap('general', 'site_header_text', $languages, ''),
                'meta_title'       => Settings::getLangMap('general', 'meta_title', $languages, ''),
                'meta_description' => Settings::getLangMap('general', 'meta_description', $languages, ''),
                'meta_keywords'    => Settings::getLangMap('general', 'meta_keywords', $languages, ''),
                'address'          => Settings::getLangMap('general', 'address', $languages, ''),
                'map_iframe'       => Settings::get('general', 'map_iframe', ''),
                'frontend_url'     => Settings::get('general', 'frontend_url', ''),
                'email'            => Settings::get('general', 'email', ''),
                'hr_email'         => Settings::get('general', 'hr_email', ''),
                'phones'           => array_values(Settings::get('general', 'phones', [])),
                'head_raw'         => Settings::get('general', 'head_raw', ''),
                'body_raw'         => Settings::get('general', 'body_raw', ''),
                'images'           => Settings::get('general', 'images', [
                    'logo'             => null,
                    'logo_dark'        => null,
                    'footer_logo'      => null,
                    'footer_logo_dark' => null,
                    'mobile_logo'      => null,
                    'mobile_logo_dark' => null,
                    'favicon'          => null,
                    'default_image'    => null,
                    'watermark'        => null,
                ]),
                'work_hours'       => Settings::get('general', 'work_hours', [
                    'mon' => '09:00 - 18:00',
                    'tue' => '09:00 - 18:00',
                    'wed' => '09:00 - 18:00',
                    'thu' => '09:00 - 18:00',
                    'fri' => '09:00 - 18:00',
                    'sat' => '09:00 - 18:00',
                    'sun' => '09:00 - 18:00',
                ])
            ],

            'og'       => [
                'title'        => Settings::getLangMap('og', 'title', $languages, ''),
                'description'  => Settings::getLangMap('og', 'description', $languages, ''),
                'type'         => Settings::get('og', 'type', 'website'),
                'image'        => Settings::get('og', 'image', null),
                'canonical'    => Settings::get('og', 'canonical', ''),
                'twitter_card' => Settings::get('og', 'twitter_card', 'summary_large_image'),
                'twitter_site' => Settings::get('og', 'twitter_site', ''),
            ],

            // SOCIAL
            'social'   => [
                'facebook'  => Settings::get('social', 'facebook', [
                    'link' => '', 'icon' => 'fab fa-facebook', 'active' => false
                ]),
                'instagram' => Settings::get('social', 'instagram', [
                    'link' => '', 'icon' => 'fab fa-instagram', 'active' => false
                ]),
                'twitter'   => Settings::get('social', 'twitter', [
                    'link' => '', 'icon' => 'fab fa-twitter', 'active' => false
                ]),
                'linkedin'  => Settings::get('social', 'linkedin', [
                    'link' => '', 'icon' => 'fab fa-linkedin', 'active' => false
                ]),
            ],

            // SMTP
            'smtp'     => [
                'driver'     => Settings::get('smtp', 'driver', 'smtp'),
                'host'       => Settings::get('smtp', 'host', ''),
                'port'       => Settings::get('smtp', 'port', 465),
                'encryption' => Settings::get('smtp', 'encryption', 'ssl'),
                'username'   => Settings::get('smtp', 'username', ''),
                'password'   => Settings::get('smtp', 'password', ''),
                'from_email' => Settings::get('smtp', 'from_email', ''),
                'from_name'  => Settings::get('smtp', 'from_name', 'Laravel'),
            ],

            // SECURITY
            'security' => [
                'max_login_attempts' => Settings::get('security', 'max_login_attempts', 5),
                'lock_minutes'       => Settings::get('security', 'lock_minutes', 15),
                'password_policy'    => Settings::get('security', 'password_policy', [
                    'min' => 8, 'upper' => true, 'digit' => true, 'symbol' => true
                ]),
                'rate_limit'         => Settings::get('security', 'rate_limit', [
                    'enabled' => true, 'max' => 60, 'window_min' => 1
                ]),
                'captcha'            => Settings::get('security', 'captcha', [
                    'enabled' => false, 'site_key' => '', 'secret_key' => ''
                ]),
            ],

            // SEO
            'seo'      => [
                'google'   => Settings::get('seo', 'google', []),
                'yandex'   => Settings::get('seo', 'yandex', []),
                'facebook' => Settings::get('seo', 'facebook', []),
                'sitemap'  => Settings::get('seo', 'sitemap', []),
                'robots'   => Settings::get('seo', 'robots', ''),
            ],

            // OAUTH
            'oauth'    => [
                'telegram' => Settings::get('oauth', 'telegram', []),
                'google'   => Settings::get('oauth', 'google', []),
                'facebook' => Settings::get('oauth', 'facebook', []),
                'linkedin' => Settings::get('oauth', 'linkedin', []),
            ],

            // SYSTEM
            'system'   => [
                'env'                 => Settings::get('system', 'env', 'development'),
                'timezone'            => Settings::get('system', 'timezone', 'Asia/Baku'),
                'date_format'         => Settings::get('system', 'date_format', 'Y-m-d'),
                'default_language_id' => Settings::get('system', 'default_language_id', 1),
                'cache'               => Settings::get('system', 'cache', [
                    'enabled' => true, 'driver' => 'file', 'ttl' => '1 hour', 'prefix' => ''
                ]),
                'queue'               => Settings::get('system', 'queue', ['driver' => 'sync', 'failed_ttl' => 30]),
                'backup'              => Settings::get('system', 'backup', [
                    'frequency' => 'daily', 'retention_days' => 7
                ]),
            ],

            // FILE MANAGER
            'file'     => [
                'max_file_size'  => Settings::get('file_manager', 'max_file_size', '10MB'),
                'max_image_size' => Settings::get('file_manager', 'max_image_size', '10MB'),
                'max_video_size' => Settings::get('file_manager', 'max_video_size', '20MB'),
                'image_quality'  => Settings::get('file_manager', 'image_quality', 85),
                'storage_driver' => Settings::get('file_manager', 'storage_driver', 'local'),
                'allowed_images' => implode(',', Settings::get('file_manager', 'allowed_images', [
                    'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'
                ])),
                'allowed_videos' => implode(',', Settings::get('file_manager', 'allowed_videos', [
                    'mp4', 'avi'
                ])),
                'allowed_files'  => implode(',', Settings::get('file_manager', 'allowed_files', [
                    'pdf', 'doc', 'docx', 'xls', 'xlsx'
                ])),
                'sizes'          => Settings::get('file_manager', 'sizes', [
                    'thumb' => ['w' => 150, 'h' => 150], 'medium' => ['w' => 300, 'h' => 300],
                    'large' => ['w' => 800, 'h' => 800]
                ]),
                'watermark'      => Settings::get('file_manager', 'watermark', [
                    'enabled' => false, 'position' => 'bottom-right', 'opacity' => 50
                ])
            ],
        ];

        $activeTab = request('tab', 'general');

        return view('admin.settings.index', compact('data', 'activeTab'));
    }

    public function update(UpdateSettingRequest $request)
    {
        $validated = $request->validated();

        // ===== GENERAL =====
        if ($gen = Arr::get($validated, 'general')) {

            // Phones
            $phones      = Arr::get($gen, 'phones', []);
            $cleanPhones = [];
            foreach ($phones as $row) {
                $num = trim((string)($row['number'] ?? ''));
                if ($num !== '') {
                    $cleanPhones[] = [
                        'is_whatsapp' => !empty($row['is_whatsapp']),
                        'label'       => (string)($row['label'] ?? ''),
                        'number'      => $num,
                    ];
                }
            }
            $gen['phones'] = $cleanPhones;

            // Images
            $images  = Settings::get('general', 'images', []);
            $imgKeys = [
                'logo',
                'logo_dark',
                'footer_logo',
                'footer_logo_dark',
                'mobile_logo',
                'mobile_logo_dark',
                'favicon',
                'default_image',
                'watermark',
            ];

            $remove = $request->input('general.images_remove', []);
            foreach ($imgKeys as $key) {
                if (!empty($remove[$key])) {
                    $images[$key] = null;
                }
            }

            $transparentKeys = [
                'logo',
                'logo_dark',
                'footer_logo',
                'footer_logo_dark',
                'mobile_logo',
                'mobile_logo_dark',
                'watermark',
                'default_image'
            ];

            foreach ($imgKeys as $key) {
                /** @var UploadedFile|null $f */
                $f = $request->file("files.general.images.$key");

                if ($f instanceof UploadedFile) {
                    $ext  = strtolower($f->getClientOriginalExtension() ?: '');
                    $mime = $f->getMimeType();

                    if (
                        in_array($ext, ['svg', 'ico'], true) || in_array($mime, [
                            'image/svg+xml', 'image/x-icon'
                        ], true)
                    ) {
                        $path         = $f->store('uploads/settings', 'public');
                        $images[$key] = $path;
                        continue;
                    }

                    $img = Image::make($f)->orientate();

                    if ($key === 'favicon') {
                        $img->resize(512, null, function ($c) {
                            $c->aspectRatio();
                            $c->upsize();
                        });
                        $binary = (string)$img->encode('png');
                        $name   = uniqid($key . '_') . '.png';

                    }
                    elseif (in_array($key, $transparentKeys, true) && in_array($ext, ['png', 'webp'], true)) {
                        $img->resize(1600, null, function ($c) {
                            $c->aspectRatio();
                            $c->upsize();
                        });
                        $binary = (string)$img->encode('png');
                        $name   = uniqid($key . '_') . '.png';

                    }
                    else {
                        $img->resize(1600, null, function ($c) {
                            $c->aspectRatio();
                            $c->upsize();
                        });
                        $binary = (string)$img->encode('jpg', 82);
                        $name   = uniqid($key . '_') . '.jpg';
                    }

                    $dir  = 'uploads/settings';
                    $path = $dir . '/' . $name;
                    \Storage::disk('public')->put($path, $binary);
                    $images[$key] = $path;
                }
            }

            $gen['images'] = $images;

            foreach ($gen as $k => $v) {
                Settings::set('general', $k, $v);
            }
        }

        // ===== OG IMAGE =====
        $removeOgImage = (bool)$request->input('og.image_remove', false);
        if ($removeOgImage) {
            Settings::set('og', 'image', null);
        }

        $f = $request->file('files.og.image');
        if ($f instanceof UploadedFile) {
            $ext  = strtolower($f->getClientOriginalExtension() ?: '');
            $mime = $f->getMimeType();

            if (in_array($ext, ['svg'], true) || in_array($mime, ['image/svg+xml'], true)) {
                $path = $f->store('uploads/settings', 'public');
                Settings::set('og', 'image', $path);
            }
            else {
                $img = Image::make($f)->orientate();
                $img->resize(1200, 630, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });

                $binary = (string)$img->encode('jpg', 82);
                $name   = uniqid('og_') . '.jpg';

                $dir  = 'uploads/settings';
                $path = $dir . '/' . $name;

                \Storage::disk('public')->put($path, $binary);
                Settings::set('og', 'image', $path);
            }
        }

        // ===== OTHER GROUPS =====
        foreach (['social', 'smtp', 'security', 'seo', 'og', 'oauth', 'system'] as $group) {
            if ($payload = Arr::get($validated, $group)) {
                foreach ($payload as $k => $v) {
                    Settings::set($group, $k, $v);
                }
            }
        }

        // ===== SQUARE =====
        if ($square = Arr::get($validated, 'square')) {
            foreach ($square as $k => $v) {
                Settings::set('square', $k, $v);
            }
        }

        // ===== FILE MANAGER =====
        if ($file = Arr::get($validated, 'file')) {
            $file['allowed_images'] = $this->csv((string)($file['allowed_images'] ?? ''));
            $file['allowed_files']  = $this->csv((string)($file['allowed_files'] ?? ''));
            $file['allowed_videos'] = $this->csv((string)($file['allowed_videos'] ?? ''));

            foreach ($file as $k => $v) {
                Settings::set('file_manager', $k, $v);
            }
        }

        $tab = $request->input('_active_tab', 'general');

        return redirect()
            ->route('admin.settings.edit', ['tab' => $tab])
            ->with('success', __('Settings updated successfully.'));
    }

    private function csv(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        return collect(explode(',', $csv))
            ->map(fn($s) => strtolower(trim((string)$s)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
