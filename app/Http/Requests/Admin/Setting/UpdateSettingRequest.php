<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Files (general images)
            'files.general.images.*' => ['nullable', 'image', 'max:25600'],
            'files.og.image' => ['nullable', 'image', 'max:25600'],


            // Settings groups
            'general'   => ['array'],
            'og'   => ['array'],
            'social'    => ['array'],
            'smtp'      => ['array'],
            'security'  => ['array'],
            'seo'       => ['array'],
            'oauth'     => ['array'],
            'system'    => ['array'],
            'file'      => ['array'],
            '_active_tab' => ['nullable', 'string'],

            'og.image_remove' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            // files.general.images.*
            'files.general.images.*.image' => __('Each uploaded file must be a valid image.'),
            'files.general.images.*.max'   => __('Each image may not be larger than :max kilobytes.'),

            // general
            'general.array' => __('The general settings data must be a valid array.'),

            // general
            'og.array' => __('The og settings data must be a valid array.'),

            // social
            'social.array' => __('The social settings data must be a valid array.'),

            // smtp
            'smtp.array' => __('The SMTP settings data must be a valid array.'),

            // security
            'security.array' => __('The security settings data must be a valid array.'),

            // seo
            'seo.array' => __('The SEO settings data must be a valid array.'),

            // oauth
            'oauth.array' => __('The OAuth settings data must be a valid array.'),

            // system
            'system.array' => __('The system settings data must be a valid array.'),

            // file
            'file.array' => __('The file manager settings data must be a valid array.'),

            // _active_tab
            '_active_tab.string' => __('The active tab value must be a valid string.'),
        ];
    }
}
