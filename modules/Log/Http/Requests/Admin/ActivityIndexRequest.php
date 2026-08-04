<?php
namespace Modules\Log\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ActivityIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('log.view') === true;
    }

    public function rules(): array
    {
        return [
            'actor_id' => ['nullable', 'integer', 'min:1'],
            'action' => ['nullable', 'string', 'max:50'],
            'module' => ['nullable', 'string', 'max:120'],
            'subject_type' => ['nullable', 'string', 'max:191'],
            'subject_id' => ['nullable', 'integer', 'min:1'],
            'ip' => ['nullable', 'string', 'max:45'],
            'route' => ['nullable', 'string', 'max:191'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'actor_id.integer' => __('Actor is invalid.'),
            'subject_id.integer' => __('Subject is invalid.'),
            'date_from.date' => __('Date from is invalid.'),
            'date_to.date' => __('Date to is invalid.'),
            'date_to.after_or_equal' => __('Date to must be after or equal date from.'),
        ];
    }
}
