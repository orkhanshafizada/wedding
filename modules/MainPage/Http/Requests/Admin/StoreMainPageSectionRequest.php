<?php

namespace Modules\MainPage\Http\Requests\Admin;

use App\Enums\StatusEnum;
use App\Models\Language;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\MainPage\Enums\MainPageSectionSourceType;
use Modules\Menu\Enums\MenuType;

class StoreMainPageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function requiredLanguageIds(): array
    {
        $ids = Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->where('is_required', 1)
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(static fn ($value): int => (int) $value)
            ->all();

        if ($ids !== []) {
            return $ids;
        }

        $fallbackId = (int) (Language::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->value('id') ?? 0);

        return $fallbackId > 0 ? [$fallbackId] : [];
    }

    protected function prepareForValidation(): void
    {
        $titles = (array) $this->input('title', []);

        foreach ($titles as $languageId => $title) {
            if ($title === null) {
                $titles[$languageId] = '';
            }
        }

        $this->merge([
            'title' => $titles,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'source_type' => [
                'required',
                'string',
                Rule::in(array_column(MainPageSectionSourceType::options(), 'value')),
            ],

            'source_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'menu_type' => [
                'nullable',
                'string',
                Rule::in(array_map(static fn (MenuType $type): string => $type->value, MenuType::cases())),
            ],

            'menu_view_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],

            'status' => [
                'required',
                Rule::in([StatusEnum::ACTIVE, StatusEnum::INACTIVE]),
            ],

            'title' => [
                'required',
                'array',
            ],

            'title.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];

        foreach ($this->requiredLanguageIds() as $languageId) {
            $rules["title.$languageId"] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'source_type.required' => __('Section source is required.'),
            'source_type.string' => __('Section source must be a string.'),
            'source_type.in' => __('Selected section source is invalid.'),

            'source_reference.string' => __('Source reference must be a string.'),
            'source_reference.max' => __('Source reference may not be greater than :max characters.'),

            'menu_type.string' => __('Menu type must be a string.'),
            'menu_type.in' => __('Selected menu type is invalid.'),

            'menu_view_type.string' => __('Menu view type must be a string.'),
            'menu_view_type.max' => __('Menu view type may not be greater than :max characters.'),

            'limit.integer' => __('Limit must be an integer.'),
            'limit.min' => __('Limit must be at least :min.'),
            'limit.max' => __('Limit may not be greater than :max.'),

            'status.required' => __('Status is required.'),
            'status.in' => __('Status is invalid.'),

            'title.required' => __('Section titles are required.'),
            'title.array' => __('Section titles must be an array.'),
            'title.*.string' => __('Section title must be a string.'),
            'title.*.max' => __('Section title may not be greater than :max characters.'),
        ];

        foreach ($this->requiredLanguageIds() as $languageId) {
            $messages["title.$languageId.required"] = __('Section title is required.');
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
            $sourceType = (string) $this->input('source_type');
            $sourceReference = trim((string) $this->input('source_reference'));
            $menuType = trim((string) $this->input('menu_type'));

            if (in_array($sourceType, [
                    MainPageSectionSourceType::BANNER->value,
                    MainPageSectionSourceType::PRODUCT_BLOCK->value,
                    MainPageSectionSourceType::MENU_TYPE->value,
                ], true) && $sourceReference === '') {
                $validator->errors()->add('source_reference', __('Source reference is required.'));
            }

            if ($sourceType === MainPageSectionSourceType::MENU_TYPE->value && $menuType === '') {
                $validator->errors()->add('menu_type', __('Menu type is required.'));
            }
        });
    }
}
