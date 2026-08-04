<?php

namespace Modules\LogosPartners\Http\Requests\Admin;

use App\Enums\StatusEnum;
use App\Models\Language;
use App\Support\Settings;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreLogosPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function activeLocaleCodes(): array
    {
        $codes = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->pluck('code')
            ->map(static fn($v): string => trim((string)$v))
            ->filter(static fn($v): bool => $v !== '')
            ->values()
            ->all();

        if ($codes !== []) {
            return $codes;
        }

        $fallback = trim((string)config('app.locale'));

        return $fallback !== '' ? [$fallback] : [];
    }

    protected function requiredLocaleCodes(): array
    {
        $codes = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', 1)
            ->orderBy('sort_order')
            ->pluck('code')
            ->map(static fn($v): string => trim((string)$v))
            ->filter(static fn($v): bool => $v !== '')
            ->values()
            ->all();

        if ($codes !== []) {
            return $codes;
        }

        $fallback = (string)(Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->value('code') ?? '');

        $fallback = trim($fallback);

        if ($fallback !== '') {
            return [$fallback];
        }

        $app = trim((string)config('app.locale'));

        return $app !== '' ? [$app] : [];
    }

    protected function prepareForValidation(): void
    {
        $data    = $this->all();
        $locales = $this->activeLocaleCodes();

        foreach (['name', 'description', 'slug', 'link'] as $group) {
            if (!isset($data[$group]) || !is_array($data[$group])) {
                $data[$group] = [];
            }

            foreach ($locales as $locale) {
                if (array_key_exists($locale, $data[$group]) && $data[$group][$locale] === null) {
                    $data[$group][$locale] = '';
                }
            }
        }

        $this->replace($data);
    }

    public function rules(): array
    {
        $allowedImages  = Settings::get('file_manager', 'allowed_images', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        $maxImageSizeKb = $this->parseSizeToKilobytes(
            (string)Settings::get('file_manager', 'max_image_size', '10MB')
        );

        $imageMimes = implode(',', array_values(array_filter(array_map('strtolower', (array)$allowedImages))));

        $rules = [
            'name'   => ['nullable', 'array'],
            'name.*' => ['nullable', 'string', 'max:255'],

            'description'   => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],

            'slug'   => ['nullable', 'array'],
            'slug.*' => ['nullable', 'string', 'max:255'],

            'link'   => ['nullable', 'array'],
            'link.*' => ['nullable', 'max:500'],

            'image' => ['required', 'file', "mimes:{$imageMimes}", "max:{$maxImageSizeKb}"],

            'is_active' => ['nullable', Rule::in([0, 1])],
        ];

        foreach ($this->requiredLocaleCodes() as $locale) {
            $rules["name.$locale"]        = ['nullable', 'string', 'max:255'];
            $rules["description.$locale"] = ['nullable', 'string'];
            $rules["slug.$locale"]        = ['nullable', 'string', 'max:255'];
            $rules["link.$locale"]        = ['nullable',  'max:500'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'name.array' => __('Name must be an array.'),
            'name.*.string' => __('Name must be a string.'),
            'name.*.max' => __('Name may not be greater than :max characters.'),

            'description.array' => __('Description must be an array.'),
            'description.*.string' => __('Description must be a string.'),

            'slug.array' => __('Slug must be an array.'),
            'slug.*.string' => __('Slug must be a string.'),
            'slug.*.max' => __('Slug may not be greater than :max characters.'),
            'slug.*.regex' => __('Slug may contain only lowercase letters, numbers, "-", "/" and "#".'),

            'link.array' => __('Link must be an array.'),
            'link.*.url' => __('Link must be a valid URL.'),
            'link.*.max' => __('Link may not be greater than :max characters.'),

            'image.required' => __('Image is required.'),
            'image.file' => __('Image must be a file.'),
            'image.mimes' => __('Image format is invalid.'),
            'image.max' => __('Image file is too large.'),

            'is_active.in' => __('Status is invalid.'),
        ];

        foreach ($this->requiredLocaleCodes() as $locale) {
            $lang = strtoupper($locale);

            $messages["name.$locale.required"] = __('Name is required (:lang).', ['lang' => $lang]);
            $messages["name.$locale.string"] = __('Name must be a string (:lang).', ['lang' => $lang]);
            $messages["name.$locale.max"] = __('Name may not be greater than :max characters (:lang).', ['max' => 255, 'lang' => $lang]);

            $messages["description.$locale.required"] = __('Description is required (:lang).', ['lang' => $lang]);
            $messages["description.$locale.string"] = __('Description must be a string (:lang).', ['lang' => $lang]);

            $messages["slug.$locale.required"] = __('Slug is required (:lang).', ['lang' => $lang]);
            $messages["slug.$locale.string"] = __('Slug must be a string (:lang).', ['lang' => $lang]);
            $messages["slug.$locale.max"] = __('Slug may not be greater than :max characters (:lang).', ['max' => 255, 'lang' => $lang]);
            $messages["slug.$locale.regex"] = __('Slug may contain only lowercase letters, numbers, "-", "/" and "#".') . " ($lang)";

            $messages["link.$locale.required"] = __('Link is required (:lang).', ['lang' => $lang]);
            $messages["link.$locale.url"] = __('Link must be a valid URL (:lang).', ['lang' => $lang]);
            $messages["link.$locale.max"] = __('Link may not be greater than :max characters (:lang).', ['max' => 500, 'lang' => $lang]);
        }

        return $messages;
    }
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => __('Validation error'),
                    'errors'  => $validator->errors()->toArray(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    private function parseSizeToKilobytes(string $val): int
    {
        $val = trim($val);

        if ($val === '') {
            return 10 * 1024;
        }

        if (preg_match('/^(\d+)\s*(kb|mb|gb)?$/i', $val, $m)) {
            $num  = (int)$m[1];
            $unit = strtolower($m[2] ?? 'mb');

            return match ($unit) {
                'kb'    => $num,
                'gb'    => $num * 1024 * 1024,
                default => $num * 1024,
            };
        }

        return (int)$val;
    }
}
