<?php

namespace Modules\Form\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormTextResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $fallbackLocale = (string) config('app.locale');

        $translation = null;

        if ($this->relationLoaded('translations')) {
            $translation = $this->translations->firstWhere('locale', $locale)
                ?? ($fallbackLocale !== $locale ? $this->translations->firstWhere('locale', $fallbackLocale) : null)
                ?? $this->translations->first();
        } else {
            $translation = $this->translations()
                ->where('locale', $locale)
                ->first()
                ?? ($fallbackLocale !== $locale ? $this->translations()->where('locale', $fallbackLocale)->first() : null)
                ?? $this->translations()->first();
        }

        return [
            'header_text' => $translation?->header_text,
            'success_text' => $translation?->success_text,
            'email_text' => $translation?->email_text,
        ];
    }
}
