<?php

namespace Modules\TeamStaff\Http\Requests\Admin;

use App\Enums\StatusEnum;
use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('teamstaff.edit') ?? true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array', 'min:1'],
            'name.*' => ['nullable', 'string', 'max:255'],

            'company' => ['nullable', 'array'],
            'company.*' => ['nullable', 'string', 'max:255'],

            'position' => ['nullable', 'array'],
            'position.*' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],

            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],

            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],

            'social_networks' => ['nullable', 'array'],
            'social_networks.facebook' => ['nullable', 'string', 'max:1000'],
            'social_networks.twitter' => ['nullable', 'string', 'max:1000'],
            'social_networks.linkedin' => ['nullable', 'string', 'max:1000'],
            'social_networks.instagram' => ['nullable', 'string', 'max:1000'],

            'existing_files' => ['nullable', 'array'],
            'existing_files.*.path' => ['required_with:existing_files', 'string', 'max:2000'],
            'existing_files.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'existing_files.*._delete' => ['nullable', 'boolean'],

            'files' => ['nullable', 'array'],
            'files.*' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx', 'max:51200'],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Name data is required.'),
            'name.array' => __('Name data must be an array.'),
            'name.min' => __('At least one name value is required.'),
            'name.*.max' => __('Name cannot be longer than 255 characters.'),

            'company.array' => __('Company data must be an array.'),
            'company.*.max' => __('Company cannot be longer than 255 characters.'),

            'position.array' => __('Position data must be an array.'),
            'position.*.max' => __('Position cannot be longer than 255 characters.'),

            'description.array' => __('Description data must be an array.'),

            'color.regex' => __('Selected color is invalid.'),

            'phone.max' => __('Phone cannot be longer than 50 characters.'),
            'mobile.max' => __('Mobile cannot be longer than 50 characters.'),
            'email.email' => __('Email must be a valid email address.'),
            'email.max' => __('Email cannot be longer than 255 characters.'),

            'profile_picture.image' => __('Profile picture must be an image.'),
            'profile_picture.mimes' => __('Profile picture format is invalid.'),
            'profile_picture.max' => __('Profile picture file is too large.'),

            'social_networks.array' => __('Social networks data must be an array.'),
            'social_networks.*.max' => __('Social network URL cannot be longer than 1000 characters.'),

            'existing_files.array' => __('Existing files data must be an array.'),
            'existing_files.*.path.required_with' => __('Existing file path is required.'),
            'existing_files.*.path.max' => __('Existing file path cannot be longer than 2000 characters.'),
            'existing_files.*.sort_order.integer' => __('Existing file order must be numeric.'),
            'existing_files.*.sort_order.min' => __('Existing file order is invalid.'),
            'existing_files.*._delete.boolean' => __('Existing file delete value is invalid.'),

            'files.array' => __('Files data must be an array.'),
            'files.*.file' => __('Uploaded item must be a valid file.'),
            'files.*.mimes' => __('Uploaded file format is invalid.'),
            'files.*.max' => __('Uploaded file is too large.'),

            'is_active.boolean' => __('Status value is invalid.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->normalizeLocalizedArray($this->input('name', [])),
            'company' => $this->normalizeLocalizedArray($this->input('company', [])),
            'position' => $this->normalizeLocalizedArray($this->input('position', [])),
            'description' => $this->normalizeLocalizedArray($this->input('description', [])),
            'color' => $this->normalizeColor($this->input('color')),
            'phone' => $this->nullableTrimmedString($this->input('phone')),
            'mobile' => $this->nullableTrimmedString($this->input('mobile')),
            'email' => $this->nullableTrimmedString($this->input('email')),
            'social_networks' => $this->normalizeSocialNetworks((array) $this->input('social_networks', [])),
            'existing_files' => $this->normalizeExistingFiles((array) $this->input('existing_files', [])),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach ($this->requiredLocaleCodes() as $locale) {
                $name = trim((string) data_get($this->input('name', []), $locale, ''));

                if ($name === '') {
                    $validator->errors()->add('name.' . $locale, __('The name field is required for each required language.') . ' (' . $locale . ')');
                }
            }
        });
    }

    private function normalizeLocalizedArray(mixed $value): array
    {
        return collect(is_array($value) ? $value : [])
            ->mapWithKeys(function ($item, $locale): array {
                $locale = trim((string) $locale);

                if ($locale === '') {
                    return [];
                }

                return [$locale => $this->nullableTrimmedString($item)];
            })
            ->all();
    }

    private function normalizeSocialNetworks(array $value): array
    {
        return collect(['facebook', 'twitter', 'linkedin', 'instagram'])
            ->mapWithKeys(fn (string $key): array => [$key => $this->nullableTrimmedString($value[$key] ?? null)])
            ->filter(fn ($value): bool => $value !== null)
            ->all();
    }

    private function normalizeExistingFiles(array $rows): array
    {
        return collect($rows)
            ->filter(fn ($row): bool => is_array($row))
            ->map(function (array $row, int|string $index): array {
                return [
                    'path' => trim((string) ($row['path'] ?? '')),
                    'sort_order' => (int) ($row['sort_order'] ?? $index),
                    '_delete' => (bool) ((int) ($row['_delete'] ?? 0) === 1),
                ];
            })
            ->filter(fn (array $row): bool => $row['path'] !== '')
            ->values()
            ->all();
    }

    private function normalizeColor(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function requiredLocaleCodes(): array
    {
        $codes = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->map(static fn ($code): string => trim((string) $code))
            ->filter()
            ->values()
            ->all();

        if ($codes !== []) {
            return $codes;
        }

        $fallbackCode = (string) Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->value('code');

        return trim($fallbackCode) !== '' ? [trim($fallbackCode)] : [];
    }
}
