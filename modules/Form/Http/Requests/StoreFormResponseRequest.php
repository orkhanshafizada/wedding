<?php

namespace Modules\Form\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormResponseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $answers = $this->input('answers');

        if (! is_array($answers)) {
            return;
        }

        $this->merge([
            'answers' => collect($answers)
                ->mapWithKeys(static function (mixed $value, mixed $key): array {
                    return [(string) $key => is_string($value) ? trim($value) : $value];
                })
                ->all(),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => [
                'bail',
                'required',
                'array',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => __('Form answers are required.'),
            'answers.array' => __('Form answers must be provided in a valid format.'),
            'answers.min' => __('At least one form answer is required.'),
        ];
    }
}