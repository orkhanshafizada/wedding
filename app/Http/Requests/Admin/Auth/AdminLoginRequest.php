<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => __('Please enter your email address.'),
            'email.email'       => __('Please enter a valid email address.'),

            'password.required' => __('Please enter your password.'),
            'password.string'   => __('The password must be a valid string.'),
        ];
    }
}
