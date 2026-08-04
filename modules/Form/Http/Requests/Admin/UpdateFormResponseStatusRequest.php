<?php

namespace Modules\Form\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Form\Models\FormResponse;

class UpdateFormResponseStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'integer',
                Rule::in([
                    FormResponse::STATUS_INACTIVE,
                    FormResponse::STATUS_ACTIVE,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => __('Status is required.'),
            'status.integer' => __('Status must be an integer.'),
            'status.in' => __('The selected status is invalid.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => (int) $this->input('status'),
            ]);
        }
    }
}