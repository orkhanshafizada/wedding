<?php

namespace App\Http\Requests\Admin\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'native_name' => ['nullable', 'string', 'max:100'],
            'code'        => [
                'required',
                'string',
                'max:20',
                Rule::unique('languages', 'code')
                    ->ignore($this->route('language'))
                    ->whereNull('deleted_at'),
            ],
            'is_rtl'      => ['required', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
            'status'      => ['required', 'in:Active,Inactive'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Please enter the language name.'),
            'name.string'   => __('The language name must be a valid string.'),
            'name.max'      => __('The language name may not be greater than :max characters.'),

            'native_name.string' => __('The native name must be a valid string.'),
            'native_name.max'    => __('The native name may not be greater than :max characters.'),

            'code.required' => __('Please enter the locale code.'),
            'code.string'   => __('The locale code must be a valid string.'),
            'code.max'      => __('The locale code may not be greater than :max characters.'),
            'code.unique'   => __('This locale code is already in use.'),

            'is_rtl.required' => __('Please specify whether the language is RTL.'),
            'is_rtl.boolean'  => __('The RTL value must be true or false.'),

            'is_required.boolean' => __('The required value must be true or false.'),

            'status.required' => __('Please select the status.'),
            'status.in'       => __('Invalid status selected.'),

            'sort_order.integer' => __('The sort order must be an integer.'),
            'sort_order.min'     => __('The sort order cannot be negative.'),
            'sort_order.max'     => __('The sort order may not be greater than :max.'),
        ];
    }
}
