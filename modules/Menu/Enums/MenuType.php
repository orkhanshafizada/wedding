<?php

namespace Modules\Menu\Enums;

enum MenuType: string
{
    case CATEGORIES    = 'categories';
    case CONTENT       = 'content';
    case CONTACTUS     = 'contact';
    case LINK          = 'link';
    case FAQ           = 'faq';
    case TEAMSTAFF     = 'teamstaff';
    case LOGOSPARTNERS = 'logospartners';
    case FORM          = 'form';
    case GRIDS         = 'grids';
    case PHOTO_GALLERY = 'photo_gallery';
    case VIDEO_GALLERY = 'video_gallery';
    case FILES         = 'files';

    public function label(): string
    {
        return match ($this) {
            self::CATEGORIES    => __('Categories'),
            self::CONTENT       => __('Content'),
            self::CONTACTUS     => __('Contact'),
            self::LINK          => __('Link'),
            self::FAQ           => __('FAQ'),
            self::TEAMSTAFF     => __('Team / Staff'),
            self::LOGOSPARTNERS => __('Logos / Partners'),
            self::FORM          => __('Form'),
            self::GRIDS         => __('Grids'),
            self::PHOTO_GALLERY => __('Photo Gallery'),
            self::VIDEO_GALLERY => __('Video Gallery'),
            self::FILES         => __('Files'),
        };
    }

    public static function options(): array
    {
        return array_map(
            static fn(self $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases()
        );
    }
}
