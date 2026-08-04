<?php

namespace Modules\AdminPermission\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'regex:/^[a-z0-9_.:-]+$/', Rule::unique('admin_permissions', 'name')],
            'display_name' => ['required', 'string', 'max:200'],
            'group' => ['required', 'string', 'max:100'],
            'scope' => ['required', 'string', Rule::in(['system', 'menu'])],
            'module' => ['required', 'string', 'max:80'],
            'action' => ['required', 'string', 'max:50'],
            'menu_id' => ['nullable', 'integer', 'exists:menus,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Permission name is required.'),
            'name.regex' => __('Permission name format is invalid.'),
            'name.unique' => __('This permission already exists.'),
            'display_name.required' => __('Display name is required.'),
            'group.required' => __('Permission group is required.'),
            'scope.in' => __('Permission scope is invalid.'),
        ];
    }
}
