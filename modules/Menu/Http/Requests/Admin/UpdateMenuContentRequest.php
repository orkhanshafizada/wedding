<?php

namespace Modules\Menu\Http\Requests\Admin;

use App\Enums\StatusEnum;
use App\Models\Language;
use App\Support\Settings;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMenuContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menu.edit') === true;
    }

    protected function requiredLocaleKeys(): array
    {
        $locales = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', 1)
            ->orderBy('sort_order')
            ->pluck('code')
            ->map(static fn ($value): string => trim((string) $value))
            ->filter(static fn ($value): bool => $value !== '')
            ->values()
            ->all();

        if ($locales !== []) {
            return $locales;
        }

        $fallbackLocale = (string) (Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->value('code') ?? '');

        $fallbackLocale = trim($fallbackLocale);

        return $fallbackLocale !== '' ? [$fallbackLocale] : [];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        if (!isset($data['title']) || !is_array($data['title'])) {
            $data['title'] = [];
        }

        if (!isset($data['description']) || !is_array($data['description'])) {
            $data['description'] = [];
        }

        $this->replace($data);
    }

    public function rules(): array
    {
        $allowedImages = Settings::get('file_manager', 'allowed_images', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        $allowedFiles = Settings::get('file_manager', 'allowed_files', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
        $maxImageSizeKb = $this->parseSizeToKilobytes(
            (string) Settings::get('file_manager', 'max_image_size', '10MB')
        );
        $maxFileSizeKb = $this->parseSizeToKilobytes(
            (string) Settings::get('file_manager', 'max_file_size', '10MB')
        );

        $imageMimes = implode(',', $this->normalizeExtensions((array) $allowedImages));
        $fileMimes = implode(',', $this->normalizeExtensions((array) $allowedFiles));

        $rules = [
            'title' => ['nullable', 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],

            'main_photo' => ['nullable', 'file', "mimes:{$imageMimes}", "max:{$maxImageSizeKb}"],

            'files' => ['nullable', 'array'],
            'files.*' => ['file', "mimes:{$fileMimes}", "max:{$maxFileSizeKb}"],
        ];

        foreach ($this->requiredLocaleKeys() as $locale) {
            $rules["title.$locale"] = ['required', 'string', 'max:255'];
            $rules["description.$locale"] = ['required', 'string'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'title.array' => __('Title must be an array.'),
            'title.*.string' => __('Title must be a string.'),
            'title.*.max' => __('Title may not be greater than :max characters.'),

            'description.array' => __('Description must be an array.'),
            'description.*.string' => __('Description must be a string.'),

            'main_photo.file' => __('Image must be a file.'),
            'main_photo.mimes' => __('Image format is invalid.'),
            'main_photo.max' => __('Image file is too large.'),

            'files.array' => __('Files must be an array.'),
            'files.*.file' => __('Invalid file.'),
            'files.*.mimes' => __('File format is invalid.'),
            'files.*.max' => __('File size must not exceed :max KB.'),
        ];

        foreach ($this->requiredLocaleKeys() as $locale) {
            $messages["title.$locale.required"] = __('Content Title') . " ($locale) " . __('must be filled');
            $messages["title.$locale.string"] = __('Content Title') . " ($locale) " . __('must be a string');
            $messages["title.$locale.max"] = __('Content Title') . " ($locale) " . __('may not be greater than :max characters.');

            $messages["description.$locale.required"] = __('Content Description') . " ($locale) " . __('must be filled');
            $messages["description.$locale.string"] = __('Content Description') . " ($locale) " . __('must be a string');
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

    private function normalizeExtensions(array $extensions): array
    {
        return array_values(array_filter(array_map(
            static fn ($extension): string => strtolower(trim((string) $extension)),
            $extensions
        )));
    }

    private function parseSizeToKilobytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 10 * 1024;
        }

        if (preg_match('/^(\d+)\s*(kb|mb|gb)?$/i', $value, $matches)) {
            $number = (int) $matches[1];
            $unit = strtolower($matches[2] ?? 'mb');

            return match ($unit) {
                'kb' => $number,
                'gb' => $number * 1024 * 1024,
                default => $number * 1024,
            };
        }

        return (int) $value;
    }
}
