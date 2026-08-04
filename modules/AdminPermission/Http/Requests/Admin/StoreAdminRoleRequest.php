<?php

namespace Modules\AdminPermission\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_.-]+$/', Rule::unique('admin_roles', 'name')],
            'display_name' => ['nullable', 'string', 'max:150'],
            'is_super_admin' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:admin_permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Role name is required.'),
            'name.regex' => __('Role name may contain only lowercase letters, numbers, dots, dashes and underscores.'),
            'name.unique' => __('This role name already exists.'),
            'permissions.array' => __('Permissions must be a valid list.'),
            'permissions.*.exists' => __('Selected permission is invalid.'),
        ];
    }
}
