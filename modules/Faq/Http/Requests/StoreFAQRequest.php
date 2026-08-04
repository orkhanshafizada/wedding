<?php

namespace Modules\FAQ\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class StoreFAQRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (auth()->guard('web')->check()) {
            return auth()->user()->can('faq.create');
        }

        return auth()->check() && auth()->user()->can('faq.create');
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
            'question' => 'required|array',
            'question.*' => 'nullable|string|max:500',
            'answer' => 'required|array',
            'answer.*' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach ($requiredLocales as $locale) {
            $rules["question.$locale"] = 'required|string|max:500';
            $rules["answer.$locale"] = 'required|string';
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
            'question.required' => 'Sual daxil edilməlidir',
            'question.array' => 'Sual formatı yanlışdır',
            'answer.required' => 'Cavab daxil edilməlidir',
            'answer.array' => 'Cavab formatı yanlışdır',
        ];

        foreach ($requiredLocales as $locale) {
            $messages["question.$locale.required"] = "Sual ($locale) mütləqdir";
            $messages["answer.$locale.required"] = "Cavab ($locale) mütləqdir";
        }

        return $messages;
    }
}
