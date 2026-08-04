<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserStatusEnum extends Enum
{
    const Active = 'Active';
    const Inactive = 'Inactive';
    const Pending = 'Pending';
    const Block = 'Block';

    public static function getDescription($value): string
    {
        return match ($value) {
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::Pending => __('Pending'),
            self::Block => __('Block'),
            default => self::getKey($value),
        };
    }
}
