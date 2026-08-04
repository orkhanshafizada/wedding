<?php
namespace App\Support;

use Illuminate\Filesystem\Filesystem;

class TranslationScanner
{
    /**
     * Regex to match __('key') and __("key")
     * - Captures single/double quoted keys without variables/concats.
     */
    private const REGEX = '/__\(\s*[\'"]([^\'"]+)[\'"]\s*[\),]/m';

    public function __construct(private readonly Filesystem $files)
    {
    }

    /**
     * Scan directories and return unique keys with file hits.
     *
     * @param  array<int, string> $paths
     * @return array<string, array<int, string>>  [key => [file1, file2, ...]]
     */
    public function scan(array $paths): array
    {
        $found = [];

        foreach ($paths as $path) {
            foreach ($this->files->allFiles($path) as $file) {
                $contents = $this->files->get($file->getPathname());
                if (! preg_match_all(self::REGEX, $contents, $matches)) {
                    continue;
                }

                foreach ($matches[1] as $key) {
                    $found[$key] ??= [];
                    $found[$key][] = $file->getPathname();
                }
            }
        }

        // Unique sources per key
        foreach ($found as $k => $list) {
            $found[$k] = array_values(array_unique($list));
        }

        return $found;
    }
}
