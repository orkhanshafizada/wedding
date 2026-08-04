<?php
namespace App\Enums;

use BenSampo\Enum\Enum;

final class StatusEnum extends Enum
{
    public const ACTIVE = 'Active';
    public const INACTIVE = 'Inactive';

    public static function normalize(mixed $value): string
    {
        if ($value === self::ACTIVE || $value === true || $value === 1 || $value === '1') {
            return self::ACTIVE;
        }

        if ($value === self::INACTIVE || $value === false || $value === 0 || $value === '0') {
            return self::INACTIVE;
        }

        return (string) $value;
    }

    public static function isActive(mixed $value): bool
    {
        return self::normalize($value) === self::ACTIVE;
    }

    public static function getDescription($value): string
    {
        return match (self::normalize($value)) {
            self::ACTIVE => 'Active',
            self::INACTIVE =>'Inactive',
            default => self::getKey($value),
        };
    }

    public static function getLabel(mixed $value): string
    {
        return match (self::normalize($value)) {
            self::ACTIVE => 'Active',
            self::INACTIVE =>'Inactive',
            default => '',
        };
    }

    public static function getBadgeClass(mixed $value): string
    {
        return match (self::normalize($value)) {
            self::ACTIVE => 'bg-success',
            self::INACTIVE => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public static function getOptions(): array
    {
        return [
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        ];
    }
}
