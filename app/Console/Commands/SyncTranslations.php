<?php
namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Translation;
use App\Support\TranslationScanner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncTranslations extends Command
{
    protected $signature = 'translations:sync
                            {--prune : Mark missing keys as Draft (do not delete)}';

    protected $description = 'Scan project for __("key") usage and sync the translations table for all active languages';

    public function handle(TranslationScanner $scanner): int
    {
        $this->info('Scanning for __("key") usage...');

        $paths = [
            base_path('resources/views'),
            base_path('app'),
            base_path('modules'),
        ];

        $keysMap = $scanner->scan($paths);
        $keys = array_keys($keysMap);

        $this->info('Found '.count($keys).' keys.');

        $locales = Language::active()->pluck('code')->all();

        DB::transaction(function () use ($keys, $keysMap, $locales): void {
            foreach ($keys as $key) {
                foreach ($locales as $locale) {
                    /** @var Translation $t */
                    $t = Translation::firstOrNew(['key' => $key, 'locale' => $locale]);
                    if (! $t->exists) {
                        $t->sources = $keysMap[$key] ?? [];
                        $t->status = 'Draft';
                        $t->save();
                    } else {
                        // Refresh sources (union)
                        $sources = collect($t->sources ?? [])->merge($keysMap[$key] ?? [])->unique()->values()->all();
                        $t->sources = $sources;
                        $t->save();
                    }
                }
            }

            if ($this->option('prune')) {
                // Mark keys that no longer exist in code as Draft (for visibility)
                Translation::whereNotIn('key', $keys)->update(['status' => 'Draft']);
            }
        });

        $this->info('Sync completed.');
        return self::SUCCESS;
    }
}
