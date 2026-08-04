<?php

namespace App\Services\AutoTranslator;

class NullDriver implements DriverInterface
{
    public function translate(string $text, ?string $source, string $target): string
    {
        return $text;
    }
}
