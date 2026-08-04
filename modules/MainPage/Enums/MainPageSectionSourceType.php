<?php

namespace Modules\MainPage\Enums;

enum MainPageSectionSourceType: string
{
    case SLIDER = 'slider';
    case BANNER = 'banner';
    case PRODUCT_BLOCK = 'product_block';
    case BRAND = 'brand';
    case SHOW_ON_MAIN_PAGE_CATEGORIES = 'show_on_main_page_categories';
    case SHOW_ON_MAIN_PAGE_SERVICES = 'show_on_main_page_services';
    case MENU_TYPE = 'menu_type';

    public function label(): string
    {
        return match ($this) {
            self::SLIDER => __('Slider'),
            self::BANNER => __('Banner'),
            self::PRODUCT_BLOCK => __('Product Blocks'),
            self::BRAND => __('Brand'),
            self::SHOW_ON_MAIN_PAGE_CATEGORIES => __('Show on Main Page Categories'),
            self::SHOW_ON_MAIN_PAGE_SERVICES => __('Show on Main Page Services'),
            self::MENU_TYPE => __('Menu Type'),
        };
    }

    public static function options(): array
    {
        return array_map(
            static fn (self $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases()
        );
    }
}
