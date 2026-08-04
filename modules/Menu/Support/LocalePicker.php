<?php

namespace Modules\Menu\Support;

class LocalePicker
{
    public static function pickArray(mixed $value, string $locale, string $fallbackLocale, array $default = []): array
    {
        $data = self::normalizeToArray($value);

        if (empty($data)) {
            return $default;
        }

        if (isset($data[$locale]) && is_array($data[$locale])) {
            return $data[$locale];
        }

        if ($fallbackLocale !== $locale && isset($data[$fallbackLocale]) && is_array($data[$fallbackLocale])) {
            return $data[$fallbackLocale];
        }

        $first = reset($data);
        return is_array($first) ? $first : $default;
    }

    public static function pickString(mixed $value, string $locale, string $fallbackLocale, ?string $default = null): ?string
    {
        $data = self::normalizeToArray($value);

        if (empty($data)) {
            return $default;
        }

        $candidate = $data[$locale] ?? null;

        if ($candidate === null && $fallbackLocale !== $locale) {
            $candidate = $data[$fallbackLocale] ?? null;
        }

        if ($candidate === null) {
            $candidate = reset($data);
        }

        if ($candidate === null) {
            return $default;
        }

        if (is_string($candidate)) {
            return $candidate;
        }

        return $default;
    }

    private static function normalizeToArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
