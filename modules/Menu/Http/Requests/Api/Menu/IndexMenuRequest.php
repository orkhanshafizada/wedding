<?php

namespace Modules\Menu\Http\Requests\Api\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Menu\Enums\MenuType;

class IndexMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['nullable', 'string', 'min:2', 'max:5'],

            'status' => ['nullable', 'boolean'],
            'in_header' => ['nullable', 'boolean'],
            'in_footer' => ['nullable', 'boolean'],
            'show_on_main_page' => ['nullable', 'boolean'],

            'type' => ['nullable', Rule::in(array_map(static fn (MenuType $type): string => $type->value, MenuType::cases()))],
            'view_type' => ['nullable', 'string', 'max:50'],

            'parent_id' => ['nullable', 'integer', 'min:1'],

            'link' => ['nullable', 'string', 'max:2048'],

            'q' => ['nullable', 'string', 'min:1', 'max:200'],

            'with_children' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'locale.string' => __('The locale must be a valid string.'),
            'locale.min' => __('The locale must be at least :min characters.', ['min' => 2]),
            'locale.max' => __('The locale may not be greater than :max characters.', ['max' => 5]),

            'status.boolean' => __('The status field must be true or false.'),
            'in_header.boolean' => __('The in_header field must be true or false.'),
            'in_footer.boolean' => __('The in_footer field must be true or false.'),
            'show_on_main_page.boolean' => __('The show_on_main_page field must be true or false.'),

            'type.in' => __('The selected type is invalid.'),

            'view_type.string' => __('The view_type must be a valid string.'),
            'view_type.max' => __('The view_type may not be greater than :max characters.', ['max' => 50]),

            'parent_id.integer' => __('The parent_id must be an integer.'),
            'parent_id.min' => __('The parent_id must be at least :min.', ['min' => 1]),

            'link.string' => __('The link must be a valid string.'),
            'link.max' => __('The link may not be greater than :max characters.', ['max' => 2048]),

            'q.string' => __('The search value must be a valid string.'),
            'q.min' => __('The search value cannot be empty.'),
            'q.max' => __('The search value may not be greater than :max characters.', ['max' => 200]),

            'with_children.boolean' => __('The with_children field must be true or false.'),
        ];
    }
}
