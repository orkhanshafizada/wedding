<?php

namespace App\Enums;

enum TranslationStatus: string
{
    case Draft = 'Draft';
    case Translated = 'Translated';

    public static function fromValue(?string $value): self
    {
        return filled($value) ? self::Translated : self::Draft;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-warning-subtle text-warning',
            self::Translated => 'bg-success-subtle text-success',
        };
    }

    public function label(): string
    {
        return $this->value;
    }
}
