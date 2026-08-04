<?php

namespace App\Services\Translations;

use App\Models\Translation;
use App\Services\AutoTranslator\DriverInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class TranslationBulkTranslateService
{
    public function __construct(
        private readonly TranslationProgressStore $progressStore,
        private readonly DriverInterface $translator
    ) {
    }

    public function start(string $source, string $target, string $driver): string
    {
        $total = Translation::query()
            ->where('locale', $target)
            ->where(function ($query) {
                $query->whereNull('value')->orWhere('value', '');
            })
            ->count();

        return $this->progressStore->start(__('Translation started...'), [
            'operation' => 'auto_translate',
            'source' => $source,
            'target' => $target,
            'driver' => $driver,
            'total' => $total,
            'processed' => 0,
            'failed' => 0,
            'last_id' => 0,
            'chunk_size' => $driver === 'google' ? 10 : 12,
        ]);
    }

    public function advance(string $token, int $adminId): void
    {
        $lock = Cache::lock('translations:auto-translate:' . $token, 20);

        if (! $lock->get()) {
            return;
        }

        try {
            $state = $this->progressStore->get($token);

            if ($state === null || ($state['done'] ?? false) === true) {
                return;
            }

            $meta = $state['meta'] ?? [];
            $operation = (string) ($meta['operation'] ?? '');

            if ($operation !== 'auto_translate') {
                return;
            }

            $source = (string) ($meta['source'] ?? '');
            $target = (string) ($meta['target'] ?? '');
            $driver = (string) ($meta['driver'] ?? 'default');
            $total = max(0, (int) ($meta['total'] ?? 0));
            $processed = max(0, (int) ($meta['processed'] ?? 0));
            $failed = max(0, (int) ($meta['failed'] ?? 0));
            $lastId = max(0, (int) ($meta['last_id'] ?? 0));
            $chunkSize = max(1, min(15, (int) ($meta['chunk_size'] ?? 8)));

            if ($total === 0) {
                $this->progressStore->done($token, __('Nothing to translate.'), true);
                return;
            }

            $rows = Translation::query()
                ->where('locale', $target)
                ->where('id', '>', $lastId)
                ->where(function ($query) {
                    $query->whereNull('value')->orWhere('value', '');
                })
                ->orderBy('id')
                ->limit($chunkSize)
                ->get(['id', 'key']);

            if ($rows->isEmpty()) {
                $message = __('Auto translation completed (:processed/:total).', [
                    'processed' => $processed,
                    'total' => $total,
                ]);

                if ($failed > 0) {
                    $message .= ' ' . __('Failed: :failed.', ['failed' => $failed]);
                }

                $this->progressStore->done($token, $message, true);
                return;
            }

            $sourceMap = Translation::query()
                ->where('locale', $source)
                ->whereIn('key', $rows->pluck('key')->all())
                ->pluck('value', 'key')
                ->all();

            $preparedRows = [];
            $currentLastId = $lastId;

            foreach ($rows as $row) {
                $currentLastId = max($currentLastId, (int) $row->id);

                $sourceValue = trim((string) ($sourceMap[$row->key] ?? ''));
                $fallbackKey = trim((string) $row->key);
                $text = $sourceValue !== '' ? $sourceValue : $fallbackKey;

                $preparedRows[] = [
                    'id' => (int) $row->id,
                    'text' => $text,
                ];
            }

            $translations = $driver === 'google'
                ? $this->translateGoogleRows($preparedRows, $source, $target)
                : $this->translateDefaultRows($preparedRows, $source, $target);

            $updates = [];

            foreach ($preparedRows as $index => $preparedRow) {
                $originalText = trim((string) $preparedRow['text']);

                if ($originalText === '') {
                    $processed++;
                    $failed++;
                    continue;
                }

                $translated = trim((string) ($translations[$index] ?? ''));

                if ($translated === '') {
                    $translated = $originalText;
                    $failed++;
                }

                $updates[] = [
                    'id' => (int) $preparedRow['id'],
                    'value' => $translated,
                    'status' => 'Translated',
                    'updated_by' => $adminId,
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

            $percent = $total > 0
                ? min(99, (int) floor(($processed / $total) * 100))
                : 99;

            $this->progressStore->putMeta($token, [
                'processed' => $processed,
                'failed' => $failed,
                'last_id' => $currentLastId,
            ]);

            $message = __('Translated :processed/:total...', [
                'processed' => $processed,
                'total' => $total,
            ]);

            if ($failed > 0) {
                $message .= ' ' . __('Failed: :failed.', ['failed' => $failed]);
            }

            $this->progressStore->set($token, $percent, $message);

            if ($processed >= $total) {
                $doneMessage = __('Auto translation completed (:processed/:total).', [
                    'processed' => $processed,
                    'total' => $total,
                ]);

                if ($failed > 0) {
                    $doneMessage .= ' ' . __('Failed: :failed.', ['failed' => $failed]);
                }

                $this->progressStore->done($token, $doneMessage, true);
            }
        } catch (Throwable $exception) {
            $this->progressStore->fail($token, $exception->getMessage());
        } finally {
            optional($lock)->release();
        }
    }

    private function translateDefaultRows(array $preparedRows, string $source, string $target): array
    {
        $translations = [];

        foreach ($preparedRows as $index => $preparedRow) {
            $text = trim((string) $preparedRow['text']);

            if ($text === '') {
                $translations[$index] = '';
                continue;
            }

            try {
                $translations[$index] = $this->translate($text, $source, $target, 'default');
            } catch (Throwable) {
                $translations[$index] = '';
            }
        }

        return $translations;
    }

    private function translateGoogleRows(array $preparedRows, string $source, string $target): array
    {
        $translations = [];

        if ($source === $target) {
            foreach ($preparedRows as $index => $preparedRow) {
                $translations[$index] = trim((string) $preparedRow['text']);
            }

            return $translations;
        }

        foreach (array_chunk($preparedRows, 4, true) as $group) {
            $responses = Http::pool(function (Pool $pool) use ($group, $source, $target) {
                $requests = [];

                foreach ($group as $index => $preparedRow) {
                    $text = trim((string) $preparedRow['text']);

                    if ($text === '') {
                        continue;
                    }

                    $requests[$index] = $pool
                        ->timeout(8)
                        ->retry(1, 200)
                        ->acceptJson()
                        ->get('https://translate.googleapis.com/translate_a/single', [
                            'client' => 'gtx',
                            'sl' => $source,
                            'tl' => $target,
                            'dt' => 't',
                            'q' => $text,
                        ]);
                }

                return $requests;
            });

            foreach ($group as $index => $preparedRow) {
                $text = trim((string) $preparedRow['text']);

                if ($text === '') {
                    $translations[$index] = '';
                    continue;
                }

                try {
                    $response = $responses[$index] ?? null;

                    if ($response === null || ! $response->successful()) {
                        $translations[$index] = '';
                        continue;
                    }

                    $data = $response->json();

                    if (! is_array($data) || ! isset($data[0]) || ! is_array($data[0])) {
                        $translations[$index] = '';
                        continue;
                    }

                    $translations[$index] = trim(collect($data[0])
                        ->map(function ($item) {
                            return is_array($item) && array_key_exists(0, $item) ? (string) $item[0] : '';
                        })
                        ->implode(''));
                } catch (Throwable) {
                    $translations[$index] = '';
                }
            }
        }

        ksort($translations);

        return $translations;
    }

    private function translate(string $text, string $source, string $target, string $driver): string
    {
        $normalizedText = trim($text);

        if ($normalizedText === '') {
            return '';
        }

        if ($source === $target) {
            return $normalizedText;
        }

        if ($driver === 'google') {
            return $this->translateWithGoogle($normalizedText, $source, $target);
        }

        $translated = $this->translator->translate($normalizedText, $source, $target);

        return is_string($translated) ? trim($translated) : '';
    }

    private function translateWithGoogle(string $text, string $source, string $target): string
    {
        $response = Http::timeout(8)
            ->retry(1, 200)
            ->acceptJson()
            ->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => $source,
                'tl' => $target,
                'dt' => 't',
                'q' => $text,
            ]);

        if (! $response->successful()) {
            return '';
        }

        $data = $response->json();

        if (! is_array($data) || ! isset($data[0]) || ! is_array($data[0])) {
            return '';
        }

        $translated = collect($data[0])
            ->map(function ($item) {
                return is_array($item) && array_key_exists(0, $item) ? (string) $item[0] : '';
            })
            ->implode('');

        return trim($translated);
    }
}
