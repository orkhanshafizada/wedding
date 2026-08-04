<?php

namespace App\Services\Upload;

use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FileUploadService
{
    private string $disk;

    private int $maxFileSizeBytes;

    private array $allowedFiles;

    public function __construct()
    {
        $this->disk = 'public';

        $this->maxFileSizeBytes = $this->parseSizeToBytes(
            (string) Settings::get('file_manager', 'max_file_size', '10MB')
        );

        $this->allowedFiles = $this->normalizeExtensions(
            Settings::get('file_manager', 'allowed_files', [
                'pdf',
                'doc',
                'docx',
                'xls',
                'xlsx',
            ])
        );
    }

    public function uploadFile(UploadedFile $file, string $dir, string $field = 'file'): string
    {
        $this->validateExtension($file, $this->allowedFiles, $field);
        $this->validateSize($file, $field);

        return $this->storeRaw($file, $dir);
    }

    public function storeRaw(UploadedFile $file, string $dir): string
    {
        $extension = $this->resolveExtension($file);

        if ($extension === '') {
            $extension = 'bin';
        }

        $filename = uniqid('f_', true) . '.' . $extension;
        $path = $this->buildPath($dir, $filename);

        $stream = fopen($file->getPathname(), 'r');

        if ($stream === false) {
            throw new RuntimeException('Failed to open uploaded file stream.');
        }

        try {
            $stored = Storage::disk($this->disk)->put($path, $stream);

            if (! $stored) {
                throw new RuntimeException('Failed to store uploaded file.');
            }

            return $path;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function validateSize(UploadedFile $file, string $field): void
    {
        if ((int) $file->getSize() > $this->maxFileSizeBytes) {
            $maxMb = round($this->maxFileSizeBytes / 1024 / 1024, 1);

            $this->fail(
                __('File is too large. Max size: :size MB', ['size' => $maxMb]),
                $field
            );
        }
    }

    private function validateExtension(UploadedFile $file, array $allowed, string $field): void
    {
        $extension = $this->resolveExtension($file);

        if (! in_array($extension, $allowed, true)) {
            $this->fail(
                __('Invalid file type. Allowed: :types', ['types' => implode(', ', $allowed)]),
                $field
            );
        }
    }

    private function resolveExtension(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: '');

        if ($extension === '') {
            $extension = strtolower($file->guessExtension() ?: '');
        }

        if ($extension === 'jpeg') {
            return 'jpg';
        }

        return $extension;
    }

    private function buildPath(string $dir, string $filename): string
    {
        $cleanDir = trim(preg_replace('#^public/?#i', '', trim($dir)), "/ \t\n\r\0\x0B");

        if ($cleanDir === '' || $cleanDir === '.' || $cleanDir === '/') {
            $cleanDir = 'uploads';
        }

        return trim($cleanDir . '/' . $filename, '/');
    }

    private function normalizeExtensions(mixed $extensions): array
    {
        if (! is_array($extensions)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static function (mixed $extension): string {
                $extension = strtolower(trim((string) $extension));

                return $extension === 'jpeg' ? 'jpg' : $extension;
            },
            $extensions
        ))));
    }

    private function parseSizeToBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 10 * 1024 * 1024;
        }

        if (preg_match('/^(\d+)\s*(kb|mb|gb)?$/i', $value, $matches)) {
            $number = (int) $matches[1];
            $unit = strtolower($matches[2] ?? 'mb');

            return match ($unit) {
                'kb' => $number * 1024,
                'gb' => $number * 1024 * 1024 * 1024,
                default => $number * 1024 * 1024,
            };
        }

        return (int) $value;
    }

    private function fail(string $message, string $field): void
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
