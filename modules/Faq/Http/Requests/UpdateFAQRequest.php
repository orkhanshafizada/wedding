<?php

namespace Modules\FAQ\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFAQRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (auth()->guard('web')->check()) {
            return auth()->user()->can('faq.edit');
        }

        return auth()->check() && auth()->user()->can('faq.edit');
    }

    public function rules(): array
    {
        $requiredLocales = Language::query()
            ->isRequired()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $rules = [
            'question' => 'sometimes|required|array',
            'question.*' => 'nullable|string|max:500',
            'answer' => 'sometimes|required|array',
            'answer.*' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach ($requiredLocales as $locale) {
            $rules["question.$locale"] = 'required_with:question|string|max:500';
            $rules["answer.$locale"] = 'required_with:answer|string';
        }

        return $rules;
    }

    public function messages(): array
    {
        $requiredLocales = Language::query()
            ->isRequired()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $messages = [
            'question.array' => 'Sual formatı yanlışdır',
            'answer.array' => 'Cavab formatı yanlışdır',
        ];

        foreach ($requiredLocales as $locale) {
            $messages["question.$locale.required_with"] = "Sual ($locale) mütləqdir";
            $messages["answer.$locale.required_with"] = "Cavab ($locale) mütləqdir";
        }

        return $messages;
    }
}
