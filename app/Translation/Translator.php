<?php
namespace App\Translation;

use Illuminate\Translation\Translator as BaseTranslator;
use Illuminate\Support\Arr;

class Translator extends BaseTranslator
{
    /**
     * Get the translation for the given key.
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true): string|array
    {
        $line = parent::get($key, $replace, $locale, $fallback);

        // Əgər parent heç nə tapmayıbsa və nəticə eyni açardırsa → özümüz yoxlayaq
        if ($line === $key) {
            [$namespace, $group, $item] = $this->parseKey($key);

            $lines = $this->loader->load($locale ?: $this->locale, $group, $namespace);
            $custom = Arr::get($lines, $item);

            if ($custom !== null) {
                return $this->makeReplacements($custom, $replace);
            }
        }

        return $line;
    }
}
