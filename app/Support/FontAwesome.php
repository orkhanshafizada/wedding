<?php

namespace App\Support;

class FontAwesome
{
    public static function versions(): array
    {
        return config('fontawesome.versions', []);
    }

    public static function versionOptions(): array
    {
        $options = [];

        foreach (self::versions() as $key => $version) {
            $options[$key] = $version['label'] ?? $key;
        }

        return $options;
    }

    public static function defaultVersion(): string
    {
        return (string) config('fontawesome.default_version', 'v6_latest');
    }

    public static function icons(?string $version = null): array
    {
        $version = $version ?: self::defaultVersion();
        $versions = self::versions();

        if (!isset($versions[$version]['file'])) {
            return [];
        }

        $file = $versions[$version]['file'];

        if (!is_file($file)) {
            return [];
        }

        $icons = require $file;

        return is_array($icons) ? array_values(array_unique(array_filter($icons))) : [];
    }
}
