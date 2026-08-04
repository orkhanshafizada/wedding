<?php

namespace Modules\Gallery\Enums;

enum AlbumItemTypeEnum: string
{
    case PHOTO = 'photo';
    case VIDEO = 'video';
    case FILE = 'file';

    public function label(): string
    {
        return match($this) {
            self::PHOTO => 'Photo',
            self::VIDEO => 'Video',
            self::FILE => 'File (PDF)',
        };
    }
}
