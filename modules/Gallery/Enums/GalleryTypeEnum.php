<?php

namespace Modules\Gallery\Enums;

enum GalleryTypeEnum: string
{
    case PHOTO_GALLERY = 'photo_gallery';
    case VIDEO_GALLERY = 'video_gallery';
    case FILES = 'files';

    public function label(): string
    {
        return match($this) {
            self::PHOTO_GALLERY => 'Photo Gallery',
            self::VIDEO_GALLERY => 'Video Gallery',
            self::FILES => 'Files',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
