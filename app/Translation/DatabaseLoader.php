<?php
namespace App\Translation;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Database\ConnectionInterface;

/**
 * DB-only Translation Loader for Laravel 12.
 *
 * Works with keys in the `translations` table:
 * - Dotted keys like "admin.settings.title"
 * - JSON-style keys without dot (group="*")
 */
class DatabaseLoader implements Loader
{
    private ConnectionInterface $db;
    private string $table;

    /** Per-request cache: ["{$locale}|{$group}" => array] */
    private array $cache = [];

    public function __construct(ConnectionInterface $db, string $table = 'translations')
    {
        $this->db    = $db;
        $this->table = $table;
    }

    public function load($locale, $group, $namespace = null): array
    {
        $cacheKey = "{$locale}|{$group}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        // JSON translations (no dots in key)
        if ($group === '*') {
            $rows = $this->db->table($this->table)
                ->select(['key', 'value'])
                ->where('locale', $locale)
                ->where('key', 'not like', '%.%')
                ->get();

            $items = [];
            foreach ($rows as $row) {
                $items[$row->key] = (string) $row->value;
            }

            return $this->cache[$cacheKey] = $items;
        }

        // Grouped keys (start with group.)
        $rows = $this->db->table($this->table)
            ->select(['key', 'value'])
            ->where('locale', $locale)
            ->where('key', 'like', $group . '.%')
            ->get();

        $tree = [];
        foreach ($rows as $row) {
            $itemPath = substr($row->key, strlen($group) + 1);
            $this->putNested($tree, $itemPath, (string) $row->value);
        }

        return $this->cache[$cacheKey] = $tree;
    }

    public function addNamespace($namespace, $hint): void
    {
        // no-op
    }

    public function addJsonPath($path): void
    {
        // no-op
    }

    public function namespaces(): array
    {
        // Laravel expects an array of registered namespaces
        return [];
    }

    private function putNested(array &$array, string $path, string $value): void
    {
        $segments = explode('.', $path);
        $ref =& $array;

        foreach ($segments as $i => $segment) {
            $last = $i === count($segments) - 1;

            if (!array_key_exists($segment, $ref)) {
                $ref[$segment] = $last ? $value : [];
            } elseif ($last) {
                $ref[$segment] = $value;
            } elseif (!is_array($ref[$segment])) {
                $ref[$segment] = ['_value' => $ref[$segment]];
            }

            $ref =& $ref[$segment];
        }
    }
}
