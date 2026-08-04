<?php

namespace Modules\Menu\Enums;

enum MenuIncludedItemType: string
{
    case MENU = 'menu';
    case SLIDER = 'slider';
    case BRAND = 'brand';
    case SELF = 'self';

    public function label(): string
    {
        return match ($this) {
            self::MENU => __('Menu'),
            self::SLIDER => __('Slider'),
            self::BRAND => __('Brand'),
            self::SELF => __('Current menu data'),
        };
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $type): string => $type->value,
            self::cases()
        );
    }
}
