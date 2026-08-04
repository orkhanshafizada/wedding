<?php

namespace App\Support;

use App\Models\Language;
use Illuminate\Support\Collection;

class AdminLanguages
{
    private Collection $languages;
    private Collection $requiredLanguages;

    public function __construct()
    {
        $this->languages = Language::query()
            ->active()
            ->orderByDesc('is_default_admin')
            ->orderBy('sort_order')
            ->get();

        $this->requiredLanguages = $this->languages
            ->where('is_required', true)
            ->values();
    }

    public function languages(): Collection
    {
        return $this->languages;
    }

    public function requiredLanguages(): Collection
    {
        return $this->requiredLanguages;
    }

    public function activeLanguageIds(): Collection
    {
        return $this->languages->pluck('id')->values();
    }

    public function requiredLanguageIds(): Collection
    {
        return $this->requiredLanguages->pluck('id')->values();
    }

    public function activeLanguageCodes(): Collection
    {
        return $this->languages->pluck('code')->filter()->values();
    }

    public function requiredLanguageCodes(): Collection
    {
        return $this->requiredLanguages->pluck('code')->filter()->values();
    }

    public function codeToIdMap(): Collection
    {
        return $this->languages
            ->filter(fn ($language) => !empty($language->code))
            ->mapWithKeys(fn ($language) => [(string) $language->code => (int) $language->id]);
    }

    public function adminDefaultLanguage(): ?Language
    {
        return $this->languages->firstWhere('is_default_admin', true)
            ?? $this->languages->first();
    }

    public function adminDefaultLanguageId(): ?int
    {
        return $this->adminDefaultLanguage()?->id;
    }

    public function adminDefaultLanguageCode(): ?string
    {
        return $this->adminDefaultLanguage()?->code;
    }
}
