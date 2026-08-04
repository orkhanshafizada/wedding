<?php

namespace Modules\Grids\Http\Requests;

use App\Enums\StatusEnum;
use App\Models\Language;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Grids\Models\GridMedia;
use Modules\Product\Models\Variation\ProductVariation;

class UpdateGridRequest extends FormRequest
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
            ->map(static fn ($value): string => trim((string) $value))
            ->filter(static fn ($value): bool => $value !== '')
            ->values()
            ->all();

        if ($codes !== []) {
            return $codes;
        }

        $fallback = trim((string) config('app.locale'));

        return $fallback !== '' ? [$fallback] : [];
    }

    protected function requiredLocaleCodes(): array
    {
        $codes = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', 1)
            ->orderBy('sort_order')
            ->pluck('code')
            ->map(static fn ($value): string => trim((string) $value))
            ->filter(static fn ($value): bool => $value !== '')
            ->values()
            ->all();

        if ($codes !== []) {
            return $codes;
        }

        $fallback = trim((string) (Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->value('code') ?? ''));

        if ($fallback !== '') {
            return [$fallback];
        }

        $appLocale = trim((string) config('app.locale'));

        return $appLocale !== '' ? [$appLocale] : [];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();
        $locales = $this->activeLocaleCodes();

        foreach ($locales as $locale) {
            foreach (['name', 'slug', 'content', 'location_or_group', 'meta_title', 'meta_description', 'meta_keywords'] as $key) {
                if (!isset($data[$key]) || !is_array($data[$key])) {
                    continue;
                }

                if (array_key_exists($locale, $data[$key]) && $data[$key][$locale] === null) {
                    $data[$key][$locale] = '';
                }
            }
        }

        $this->replace($data);
    }

    public function rules(): array
    {
        $locales = $this->activeLocaleCodes();
        $requiredLocales = $this->requiredLocaleCodes();

        $rules = [
            'datetime1' => ['nullable', 'date'],
            'datetime2' => ['nullable', 'date'],
            'banner' => ['nullable', 'image', 'max:10240'],
            'remove_banner' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],

            'media_existing' => ['nullable', 'array'],
            'media_existing.*.id' => ['required', 'integer'],
            'media_existing.*._delete' => ['nullable', 'in:0,1'],
            'media_existing.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'media_existing.*.is_main' => ['nullable', 'in:0,1'],

            'media_files' => ['nullable', 'array'],
            'media_files.*' => ['file', 'max:10240'],
            'media_new_main' => ['nullable', 'integer', 'min:0'],

            'related_product_variation_ids' => ['nullable', 'array'],
            'related_product_variation_ids.*' => ['integer', 'distinct', 'exists:product_variations,id'],
        ];

        foreach ($locales as $locale) {
            $rules["name.$locale"] = ['nullable', 'string', 'max:500'];
            $rules["slug.$locale"] = ['nullable', 'string', 'max:500', 'regex:/^[a-z0-9\/\-]+$/'];
            $rules["content.$locale"] = ['nullable', 'string'];
            $rules["location_or_group.$locale"] = ['nullable', 'string', 'max:500'];
            $rules["meta_title.$locale"] = ['nullable', 'string', 'max:255'];
            $rules["meta_description.$locale"] = ['nullable', 'string', 'max:500'];
            $rules["meta_keywords.$locale"] = ['nullable', 'string', 'max:500'];
        }

        foreach ($requiredLocales as $locale) {
            $rules["name.$locale"] = ['required', 'string', 'max:500'];
            $rules["slug.$locale"] = ['required', 'string', 'max:500', 'regex:/^[a-z0-9\/\-]+$/'];
            $rules["content.$locale"] = ['required', 'string'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'slug.*.regex' => __('Slug may contain only lowercase letters, numbers, "-" and "/".'),
            'banner.image' => __('Banner must be an image.'),
            'banner.max' => __('Banner size must not exceed :max KB.'),
            'media_files.*.max' => __('File size must not exceed :max KB.'),
        ];

        foreach ($this->requiredLocaleCodes() as $locale) {
            $messages["name.$locale.required"] = __('Name is required (:lang).', ['lang' => strtoupper($locale)]);
            $messages["slug.$locale.required"] = __('Slug is required (:lang).', ['lang' => strtoupper($locale)]);
            $messages["content.$locale.required"] = __('Content is required (:lang).', ['lang' => strtoupper($locale)]);
        }

        return $messages;
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => __('Validation error'),
                    'errors' => $validator->errors()->toArray(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $menu = $this->route('menu');
            $menuId = (int) ($menu?->id ?? 0);

            $grid = $this->route('grid');
            $gridId = is_object($grid) ? (int) ($grid->id ?? 0) : (int) $grid;

            $locales = $this->activeLocaleCodes();
            $requiredLocales = $this->requiredLocaleCodes();

            $seen = [];

            foreach ($locales as $locale) {
                $slug = trim((string) data_get($this->input('slug', []), $locale, ''));

                if ($slug === '') {
                    continue;
                }

                $duplicateKey = $locale . '|' . mb_strtolower($slug);

                if (isset($seen[$duplicateKey])) {
                    $validator->errors()->add("slug.$locale", __('This slug is duplicated.'));
                    continue;
                }

                $seen[$duplicateKey] = true;

                if ($menuId <= 0) {
                    continue;
                }

                $exists = DB::table('grids')
                    ->where('menu_id', $menuId)
                    ->where('id', '!=', $gridId)
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.\"{$locale}\"')) = ?", [$slug])
                    ->exists();

                if ($exists) {
                    $validator->errors()->add("slug.$locale", __('This slug is already in use.'));
                }
            }

            foreach ($requiredLocales as $locale) {
                $slug = trim((string) data_get($this->input('slug', []), $locale, ''));

                if ($slug === '') {
                    $validator->errors()->add(
                        "slug.$locale",
                        __('Slug is required (:lang).', ['lang' => strtoupper($locale)])
                    );
                }
            }

            $mainIndex = $this->input('media_new_main');
            $mainIndex = $mainIndex === '' || $mainIndex === null ? null : (int) $mainIndex;

            if ($mainIndex !== null) {
                $files = array_values(array_filter(
                    (array) $this->file('media_files', []),
                    static fn ($file): bool => $file instanceof UploadedFile
                ));

                if (!isset($files[$mainIndex])) {
                    $validator->errors()->add('media_new_main', __('Selected main image index is invalid.'));
                    return;
                }

                $selectedFile = $files[$mainIndex];
                $mimeType = (string) $selectedFile->getMimeType();

                if (!str_starts_with($mimeType, 'image/')) {
                    $validator->errors()->add('media_new_main', __('Main file must be an image.'));
                }
            }

            $existingMedia = (array) $this->input('media_existing', []);
            $selectedExistingMainIds = [];

            foreach ($existingMedia as $mediaId => $row) {
                $mediaId = (int) $mediaId;

                if ((string) ($row['_delete'] ?? '0') === '1') {
                    continue;
                }

                if ((int) ($row['is_main'] ?? 0) !== 1) {
                    continue;
                }

                $media = GridMedia::query()->whereKey($mediaId)->where('grid_id', $gridId)->first();

                if (!$media || $media->type !== 'image') {
                    $validator->errors()->add("media_existing.$mediaId.is_main", __('Main file must be an image.'));
                    continue;
                }

                $selectedExistingMainIds[] = $mediaId;
            }

            if (count($selectedExistingMainIds) > 1) {
                $validator->errors()->add('media_existing', __('Only one main image can be selected.'));
            }

            $variationIds = array_values(array_unique(array_map('intval', (array) $this->input('related_product_variation_ids', []))));
            if ($variationIds === []) {
                return;
            }

            $variations = ProductVariation::query()
                ->whereIn('id', $variationIds)
                ->get(['id', 'product_id']);

            if ($variations->count() !== count($variationIds)) {
                $validator->errors()->add('related_product_variation_ids', __('One or more selected related products are invalid.'));
                return;
            }

            $productIds = $variations->pluck('product_id')->map(fn ($id) => (int) $id)->all();

            if (count($productIds) !== count(array_unique($productIds))) {
                $validator->errors()->add('related_product_variation_ids', __('Only one variation can be selected per product.'));
            }
        });
    }
}
