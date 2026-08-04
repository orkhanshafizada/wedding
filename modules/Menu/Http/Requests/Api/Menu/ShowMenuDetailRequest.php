<?php

namespace Modules\Menu\Http\Requests\Api\Menu;

use Illuminate\Foundation\Http\FormRequest;

class ShowMenuDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'link'      => ['required', 'string', 'max:1000'],
            'data_slug' => ['nullable', 'string', 'max:1000'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
            'page'      => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'link.required'    => __('Menu link (slug) tələb olunur.'),
            'link.string'      => __('Menu link düzgün formatda deyil.'),
            'data_slug.string' => __('Data slug düzgün formatda deyil.'),
            'per_page.integer' => __('per_page düzgün formatda deyil.'),
            'per_page.min'     => __('per_page ən az 1 olmalıdır.'),
            'per_page.max'     => __('per_page maksimum 100 ola bilər.'),
            'page.integer'     => __('page düzgün formatda deyil.'),
            'page.min'         => __('page ən az 1 olmalıdır.'),
        ];
    }
}
