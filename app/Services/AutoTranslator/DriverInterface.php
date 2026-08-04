<?php

namespace App\Services\AutoTranslator;

interface DriverInterface
{
    public function translate(string $text, ?string $source, string $target): string;
}
