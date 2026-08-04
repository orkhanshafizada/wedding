<?php

namespace Modules\AdminPermission\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('admin')?->id;

        return [
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'string', Rule::in(['Active', 'Inactive', 'Pending'])],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:admin_roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => __('Full name is required.'),
            'email.required' => __('Email is required.'),
            'email.email' => __('Email must be a valid email address.'),
            'email.unique' => __('This email is already used.'),
            'password.min' => __('Password must contain at least :min characters.'),
            'password.confirmed' => __('Password confirmation does not match.'),
            'role_ids.required' => __('Please select at least one role.'),
            'role_ids.*.exists' => __('Selected role is invalid.'),
        ];
    }
}
