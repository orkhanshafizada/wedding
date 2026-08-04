<?php

namespace App\Jobs\Translations;

use App\Models\Translation;
use App\Services\AutoTranslator\DriverInterface;
use App\Services\Translations\TranslationProgressStore;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class AutoTranslateMissingJob
{
    use Dispatchable;
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly int $adminId,
        private readonly string $source,
        private readonly string $target,
        private readonly string $driver
    ) {
    }

    public function handle(TranslationProgressStore $progressStore, DriverInterface $translator): void
    {
        try {
            $progressStore->set($this->token, 6, __('Collecting missing translations...'));

            $missing = Translation::query()
                ->where('locale', $this->target)
                ->where(function ($query) {
                    $query->whereNull('value')->orWhere('value', '');
                })
                ->orderBy('id')
                ->get(['id', 'key']);

            $total = $missing->count();

            if ($total === 0) {
                $progressStore->done($this->token, __('Nothing to translate.'), true);
                return;
            }

            $progressStore->set($this->token, 12, __('Preparing source values...'));

            $sourceMap = Translation::query()
                ->where('locale', $this->source)
                ->whereIn('key', $missing->pluck('key')->all())
                ->pluck('value', 'key')
                ->all();

            $processed = 0;

            $missing->chunk(40)->each(function ($chunk) use (&$processed, $total, $sourceMap, $translator, $progressStore) {
                $updates = [];

                foreach ($chunk as $row) {
                    $sourceValue = isset($sourceMap[$row->key]) ? trim((string) $sourceMap[$row->key]) : '';
                    $fallbackKey = trim((string) $row->key);
                    $text = $sourceValue !== '' ? $sourceValue : $fallbackKey;

                    if ($text === '') {
                        $processed++;
                        continue;
                    }

                    $translated = $this->translateText($text, $translator);

                    if ($translated === '') {
                        $translated = $text;
                    }

                    $updates[] = [
                        'id' => (int) $row->id,
                        'value' => $translated,
                        'status' => 'Translated',
                        'updated_by' => $this->adminId,
                        'updated_at' => now(),
                    ];

                    $processed++;
                }

                DB::transaction(function () use ($updates) {
                    foreach ($updates as $update) {
                        DB::table('translations')
                            ->where('id', $update['id'])
                            ->update([
                                'value' => $update['value'],
                                'status' => $update['status'],
                                'updated_by' => $update['updated_by'],
                                'updated_at' => $update['updated_at'],
                            ]);
                    }
                });

                $percent = (int) round(12 + (($processed / max(1, $total)) * 84));
                $progressStore->set($this->token, $percent, __('Translated :processed/:total...', [
                    'processed' => $processed,
                    'total' => $total,
                ]));
            });

            $progressStore->done($this->token, __('Auto translation completed (:total).', [
                'total' => $total,
            ]), true);
        } catch (Throwable $exception) {
            $progressStore->fail($this->token, $exception->getMessage());
        }
    }

    private function translateText(string $text, DriverInterface $translator): string
    {
        $normalizedText = trim($text);

        if ($normalizedText === '') {
            return '';
        }

        if ($this->source === $this->target) {
            return $normalizedText;
        }

        if ($this->driver === 'google') {
            return $this->translateWithGoogle($normalizedText);
        }

        $translated = $translator->translate($normalizedText, $this->source, $this->target);

        return is_string($translated) ? trim($translated) : '';
    }

    private function translateWithGoogle(string $text): string
    {
        $response = Http::timeout(30)
            ->acceptJson()
            ->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => $this->source,
                'tl' => $this->target,
                'dt' => 't',
                'q' => $text,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(__('Google translate request failed.'));
        }

        $data = $response->json();

        if (! is_array($data) || ! isset($data[0]) || ! is_array($data[0])) {
            throw new \RuntimeException(__('Google translate response is invalid.'));
        }

        $translated = collect($data[0])
            ->map(function ($item) {
                return is_array($item) && isset($item[0]) ? (string) $item[0] : '';
            })
            ->implode('');

        return trim($translated);
    }
}
