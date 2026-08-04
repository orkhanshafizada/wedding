<?php

namespace App\Http\Requests\Admin\Translations;

use App\Models\Translation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255'],
            'translations' => ['required', 'array'],
            'translations.*.value' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'key' => __('Key'),
            'translations' => __('Translations'),
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => __('Translation key is required.'),
            'translations.required' => __('Translations data is required.'),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Translation|null $translation */
            $translation = $this->route('translation');

            $newKey = trim((string) $this->input('key'));
            $currentKey = (string) ($translation?->key ?? '');

            if ($newKey === '') {
                return;
            }

            $exists = \App\Models\Translation::query()
                ->where('key', $newKey)
                ->when($currentKey !== '', function ($query) use ($currentKey) {
                    $query->where('key', '!=', $currentKey);
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add('key', __('This translation key already exists.'));
            }
        });
    }
}
