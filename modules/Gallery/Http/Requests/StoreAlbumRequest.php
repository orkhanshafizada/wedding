<?php

namespace Modules\Gallery\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class StoreAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requiredLocales = Language::where('is_required', true)->pluck('code')->toArray();

        $rules = [
            'show_album' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'name' => 'required|array',
        ];

        foreach ($requiredLocales as $locale) {
            $rules["name.{$locale}"] = 'required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.*.required' => __('The name field is required'),
            'cover_image.image' => __('The file must be an image'),
            'cover_image.max' => __('The image may not be greater than 5MB'),
        ];
    }
}
