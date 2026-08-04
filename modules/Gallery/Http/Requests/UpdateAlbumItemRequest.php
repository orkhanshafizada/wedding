<?php

namespace Modules\Gallery\Http\Requests;

use App\Enums\StatusEnum;
use App\Models\Language;
use App\Support\Settings;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAlbumItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'publication' => $this->boolean('publication', false),
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'is_active' => ['required', 'boolean'],
            'publication' => ['nullable', 'boolean'],
            'title' => ['nullable', 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
        ];

//        foreach ($this->requiredLanguageCodes() as $locale) {
//            $rules["title.$locale"] = ['required', 'string', 'max:255'];
//        }

        $rules['file'] = match ($this->menuType()) {
            'photo_gallery' => $this->uploadRules(
                false,
                'allowed_images',
                ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
                'max_image_size',
                '10MB'
            ),
            'video_gallery' => $this->uploadRules(
                false,
                'allowed_videos',
                ['mp4', 'avi'],
                'max_video_size',
                '20MB'
            ),
            'files' => $this->uploadRules(
                false,
                'allowed_files',
                ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
                'max_file_size',
                '10MB'
            ),
            default => ['nullable', 'file'],
        };

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'is_active.required' => __('Status is required.'),
            'is_active.boolean' => __('Status value is invalid.'),
            'publication.boolean' => __('Publication value is invalid.'),
            'title.array' => __('Title translations must be a valid array.'),
            'title.*.string' => __('Each title must be a valid string.'),
            'title.*.max' => __('Each title may not be greater than :max characters.'),
            'description.array' => __('Description translations must be a valid array.'),
            'description.*.string' => __('Each description must be a valid string.'),
            'file.file' => __('Uploaded file is invalid.'),
            'file.mimes' => __('Invalid file type.'),
            'file.max' => __('The uploaded file size is too large.'),
        ];

        foreach ($this->requiredLanguageCodes() as $locale) {
            $messages["title.$locale.required"] = __('Title is required for required languages.');
        }

        return $messages;
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->ajax()) {
            throw new HttpResponseException(
                response()->json([
                    'message' => __('Validation error.'),
                    'errors' => $validator->errors()->toArray(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    private function menuType(): string
    {
        $menu = $this->route('menu');

        return $menu->type instanceof \BackedEnum
            ? $menu->type->value
            : (string) $menu->type;
    }

    private function requiredLanguageCodes(): array
    {
        $codes = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', true)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->pluck('code')
            ->map(static fn ($value): string => (string) $value)
            ->all();

        if ($codes !== []) {
            return $codes;
        }

        $fallback = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->value('code');

        return $fallback ? [(string) $fallback] : [];
    }

    private function uploadRules(
        bool $required,
        string $allowedKey,
        array $defaultExtensions,
        string $maxKey,
        string $defaultMax
    ): array {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:' . implode(',', $this->settingsList($allowedKey, $defaultExtensions)),
            'max:' . $this->settingsMaxKilobytes($maxKey, $defaultMax),
        ];
    }

    private function settingsList(string $key, array $default): array
    {
        $value = Settings::get('file_manager', $key, $default);

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return collect((array) $value)
            ->map(static fn ($item): string => strtolower(trim((string) $item)))
            ->filter()
            ->values()
            ->all();
    }

    private function settingsMaxKilobytes(string $key, string $default): int
    {
        return (int) ceil($this->parseSizeToBytes((string) Settings::get('file_manager', $key, $default)) / 1024);
    }

    private function parseSizeToBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 10 * 1024 * 1024;
        }

        if (preg_match('/^(\d+)\s*(kb|mb|gb)?$/i', $value, $matches)) {
            $number = (int) $matches[1];
            $unit = strtolower($matches[2] ?? 'mb');

            return match ($unit) {
                'kb' => $number * 1024,
                'gb' => $number * 1024 * 1024 * 1024,
                default => $number * 1024 * 1024,
            };
        }

        return (int) $value;
    }
}
