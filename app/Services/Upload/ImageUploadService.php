<?php

namespace App\Services\Upload;

use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use RuntimeException;
use Throwable;

class ImageUploadService
{
    private string $disk;

    private int $maxImageSizeBytes;

    private array $allowedImages;

    private int $quality;

    public function __construct()
    {
        $this->disk = 'public';

        $this->maxImageSizeBytes = $this->parseSizeToBytes(
            (string) Settings::get('file_manager', 'max_image_size', '10MB')
        );

        $this->allowedImages = $this->normalizeExtensions(
            Settings::get('file_manager', 'allowed_images', [
                'jpg',
                'jpeg',
                'png',
                'gif',
                'webp',
                'svg',
            ])
        );

        $this->quality = max(1, min(100, (int) Settings::get('file_manager', 'image_quality', 85)));
    }

    public function uploadAvatar(UploadedFile $file, string $dir, string $field = 'avatar'): string
    {
        $this->validateImageExtension($file, $field);
        $this->validateImageSize($file, $field);

        $image = $this->makeImage($file, $field);

        $image->fit(600, 600);

        $canvas = Image::canvas(600, 600, '#ffffff');
        $canvas->insert($image, 'center');

        $filename = uniqid('avatar_', true) . '.jpg';
        $path = $this->buildPath($dir, $filename);

        Storage::disk($this->disk)->put(
            $path,
            (string) $canvas->encode('jpg', $this->quality)
        );

        return $path;
    }

    public function uploadImage(UploadedFile $file, string $dir, string $field = 'image'): string
    {
        $this->validateImageExtension($file, $field);
        $this->validateImageSize($file, $field);

        $extension = $this->resolveExtension($file);

        if (in_array($extension, ['svg', 'gif'], true)) {
            return $this->storeRawImage($file, $dir, $extension);
        }

        $image = $this->makeImage($file, $field);

        $filename = uniqid('img_', true) . '.' . $extension;
        $path = $this->buildPath($dir, $filename);

        Storage::disk($this->disk)->put(
            $path,
            (string) $image->encode($this->imageEncodeFormat($extension), $this->quality)
        );

        return $path;
    }

    private function makeImage(UploadedFile $file, string $field)
    {
        try {
            $filePath = $file->getRealPath();

            if ($filePath && file_exists($filePath) && is_readable($filePath)) {
                return Image::make($filePath)->orientate();
            }

            return Image::make($file->get())->orientate();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "Failed to process uploaded image ({$field}): {$exception->getMessage()}",
                previous: $exception
            );
        }
    }

    private function storeRawImage(UploadedFile $file, string $dir, string $extension): string
    {
        $filename = uniqid('img_', true) . '.' . $extension;
        $path = $this->buildPath($dir, $filename);

        $stream = fopen($file->getPathname(), 'r');

        if ($stream === false) {
            throw new RuntimeException('Failed to open uploaded image stream.');
        }

        try {
            $stored = Storage::disk($this->disk)->put($path, $stream);

            if (! $stored) {
                throw new RuntimeException('Failed to store uploaded image.');
            }

            return $path;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function validateImageExtension(UploadedFile $file, string $field): void
    {
        $extension = $this->resolveExtension($file);

        if (! in_array($extension, $this->allowedImages, true)) {
            $this->fail(
                __('Invalid image type. Allowed: :types', ['types' => implode(', ', $this->allowedImages)]),
                $field
            );
        }
    }

    private function validateImageSize(UploadedFile $file, string $field): void
    {
        if ((int) $file->getSize() > $this->maxImageSizeBytes) {
            $maxMb = round($this->maxImageSizeBytes / 1024 / 1024, 1);

            $this->fail(
                __('Image is too large. Max size: :size MB', ['size' => $maxMb]),
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

    private function imageEncodeFormat(string $extension): string
    {
        return match ($extension) {
            'jpg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => $extension,
        };
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
