<?php

namespace Modules\Grids\Enums;

enum GridType: string
{
    case DEFAULT = 'default';
    case BLOG = 'blog';
    case NEWS = 'news';
    case CAMPAIGN = 'campaign';
    case BRAND_NEWS = 'brand-news';
}
