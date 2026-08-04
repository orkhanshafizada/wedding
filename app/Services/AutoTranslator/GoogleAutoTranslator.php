<?php

namespace App\Services\AutoTranslator;

use Stichoza\GoogleTranslate\GoogleTranslate;

class GoogleAutoTranslator implements DriverInterface
{
    public function translate(string $text, ?string $source, string $target): string
    {
        $normalizedText = trim($text);

        if ($normalizedText === '') {
            return '';
        }

        if ($source !== null && $source === $target) {
            return $normalizedText;
        }

        $translator = new GoogleTranslate();

        $translator->setSource(
            $source !== null && trim($source) !== ''
                ? trim($source)
                : null
        );

        $translator->setTarget(trim($target));

        return trim((string) $translator->translate($normalizedText));
    }
}
