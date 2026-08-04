<?php

namespace Modules\MainPage\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMainPageSectionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order'              => ['required', 'array'],
            'order.*.id'         => ['required', 'integer', 'exists:main_page_sections,id'],
            'order.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'order.required'              => __('Order data is required.'),
            'order.array'                 => __('Order data must be an array.'),
            'order.*.id.required'         => __('Section id is required.'),
            'order.*.id.integer'          => __('Section id must be an integer.'),
            'order.*.id.exists'           => __('Selected section is invalid.'),
            'order.*.sort_order.required' => __('Sort order is required.'),
            'order.*.sort_order.integer'  => __('Sort order must be an integer.'),
            'order.*.sort_order.min'      => __('Sort order must be at least :min.'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => __('Validation error'),
                'errors'  => $validator->errors()->toArray(),
            ], 422)
        );
    }
}
