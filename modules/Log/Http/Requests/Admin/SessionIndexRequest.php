<?php
namespace Modules\Log\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SessionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('log.session.view') === true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'min:1'],
            'ip' => ['nullable', 'string', 'max:45'],
            'device_type' => ['nullable', 'string', 'max:30'],
            'browser' => ['nullable', 'string', 'max:120'],
            'os' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.integer' => __('User is invalid.'),
            'ip.max' => __('IP is too long.'),
            'date_from.date' => __('Date from is invalid.'),
            'date_to.date' => __('Date to is invalid.'),
            'date_to.after_or_equal' => __('Date to must be after or equal date from.'),
        ];
    }
}
