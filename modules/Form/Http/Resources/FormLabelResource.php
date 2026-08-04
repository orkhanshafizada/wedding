<?php

namespace Modules\Form\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Form\Models\FormLabelTranslation;

class FormLabelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $translation = $this->resolveTranslation();

        return [
            'id' => (int) $this->id,
            'type' => (string) $this->type,
            'is_required' => (bool) $this->is_required,
            'send_text_mail' => (bool) $this->send_text_mail,
            'sort_order' => (int) $this->sort_order,
            'name' => (string) ($translation?->name ?? ''),
            'information' => $translation?->information,
            'options' => $this->parseOptions($translation?->information),
        ];
    }

    private function resolveTranslation(): ?FormLabelTranslation
    {
        $locale = app()->getLocale();
        $fallbackLocale = (string) config('app.locale');

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?? ($fallbackLocale !== $locale ? $this->translations->firstWhere('locale', $fallbackLocale) : null)
                ?? $this->translations->first();
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?? ($fallbackLocale !== $locale ? $this->translations()->where('locale', $fallbackLocale)->first() : null)
            ?? $this->translations()->first();
    }

    private function parseOptions(?string $information): array
    {
        $information = trim((string) $information);

        if ($information === '') {
            return [];
        }

        $decoded = json_decode($information, true);

        if (is_array($decoded)) {
            return $this->normalizeOptionsArray($decoded);
        }

        $lines = preg_split('/\R/u', $information) ?: [];
        $lines = array_values(array_filter(array_map('trim', $lines), static fn ($v) => $v !== ''));

        if (empty($lines)) {
            return [];
        }

        $options = [];
        foreach ($lines as $line) {
            if (str_contains($line, '|')) {
                [$value, $label] = array_map('trim', explode('|', $line, 2));
                $options[] = ['value' => $value, 'label' => $label !== '' ? $label : $value];
                continue;
            }

            $options[] = ['value' => $line, 'label' => $line];
        }

        return $options;
    }

    private function normalizeOptionsArray(array $decoded): array
    {
        $options = [];

        foreach ($decoded as $key => $value) {
            if (is_array($value)) {
                $val = (string) ($value['value'] ?? $value['id'] ?? $value['key'] ?? $key);
                $lab = (string) ($value['label'] ?? $value['name'] ?? $value['title'] ?? $val);
                $options[] = ['value' => $val, 'label' => $lab];
                continue;
            }

            if (is_string($value) || is_numeric($value)) {
                if (is_string($key)) {
                    $options[] = ['value' => (string) $key, 'label' => (string) $value];
                } else {
                    $options[] = ['value' => (string) $value, 'label' => (string) $value];
                }
            }
        }

        return $options;
    }
}
