<?php

namespace App\Jobs\Translations;

use App\Services\Translations\TranslationProgressStore;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ImportTranslationsJob
{
    use Dispatchable;
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly int $adminId,
        private readonly string $filePath,
        private readonly string $mode
    ) {
    }

    public function handle(TranslationProgressStore $progressStore): void
    {
        try {
            $progressStore->set($this->token, 5, __('Opening Excel...'));

            $spreadsheet = IOFactory::load($this->filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) < 2) {
                $progressStore->done($this->token, __('Empty file.'), true);
                return;
            }

            $headers = $rows[1];
            $map = $this->mapHeaders($headers);

            foreach (['key', 'locale', 'value'] as $required) {
                if (!isset($map[$required])) {
                    $progressStore->fail($this->token, __('Missing required column: :column', ['column' => $required]));
                    return;
                }
            }

            $data = [];

            for ($i = 2; $i <= count($rows); $i++) {
                $row = $rows[$i] ?? null;

                if (!is_array($row)) {
                    continue;
                }

                $key = trim((string) ($row[$map['key']] ?? ''));
                $locale = trim((string) ($row[$map['locale']] ?? ''));
                $value = (string) ($row[$map['value']] ?? '');

                if ($key === '' || $locale === '') {
                    continue;
                }

                $data[] = [
                    'key' => $key,
                    'locale' => $locale,
                    'value' => $value,
                    'status' => trim($value) !== '' ? 'Translated' : 'Draft',
                    'updated_by' => $this->adminId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            $total = count($data);

            if ($total === 0) {
                $progressStore->done($this->token, __('No valid rows found.'), true);
                return;
            }

            $progressStore->set($this->token, 15, __('Parsed :total rows...', ['total' => $total]));

            $processed = 0;

            foreach (array_chunk($data, 500) as $chunk) {
                DB::transaction(function () use ($chunk) {
                    if ($this->mode === 'only_empty') {
                        $this->applyOnlyEmpty($chunk);
                        return;
                    }

                    DB::table('translations')->upsert(
                        $chunk,
                        ['key', 'locale'],
                        ['value', 'status', 'updated_by', 'updated_at']
                    );
                });

                $processed += count($chunk);

                $percent = (int) round(15 + (($processed / max(1, $total)) * 80));
                $progressStore->set($this->token, $percent, __('Imported :processed/:total...', [
                    'processed' => $processed,
                    'total' => $total,
                ]));
            }

            $progressStore->done($this->token, __('Import completed (:total).', [
                'total' => $total,
            ]), true);
        } catch (Throwable $exception) {
            $progressStore->fail($this->token, $exception->getMessage());
        } finally {
            @unlink($this->filePath);
        }
    }

    private function mapHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $column => $value) {
            $normalized[strtolower(trim((string) $value))] = $column;
        }

        $map = [];

        foreach (['key', 'locale', 'value', 'status'] as $name) {
            if (isset($normalized[$name])) {
                $map[$name] = $normalized[$name];
            }
        }

        return $map;
    }

    private function applyOnlyEmpty(array $chunk): void
    {
        foreach ($chunk as $row) {
            $key = (string) $row['key'];
            $locale = (string) $row['locale'];
            $value = (string) $row['value'];

            if (trim($value) === '') {
                continue;
            }

            DB::table('translations')
                ->where('key', $key)
                ->where('locale', $locale)
                ->where(function ($query) {
                    $query->whereNull('value')->orWhere('value', '');
                })
                ->update([
                    'value' => $value,
                    'status' => 'Translated',
                    'updated_by' => $row['updated_by'],
                    'updated_at' => $row['updated_at'],
                ]);
        }
    }
}
