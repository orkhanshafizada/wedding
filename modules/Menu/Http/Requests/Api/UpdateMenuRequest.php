<?php

namespace Modules\Menu\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['nullable', 'string', 'max:255'],
            'status'    => ['required', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
        ];
    }
}
