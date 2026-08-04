<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class GuestToken
{
    public const HEADER = 'X-Guest-Token';

    public static function fromRequest(Request $request): ?string
    {
        $token = trim((string) $request->header(self::HEADER, ''));

        return $token !== '' ? $token : null;
    }

    public static function generate(): string
    {
        return Str::random(40);
    }
}
