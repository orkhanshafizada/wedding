<?php
namespace App\Services\Upload;

use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VideoUploadService
{
    private string $disk;
    private int $maxFileSizeBytes;
    /** @var array<int,string> */
    private array $allowedVideos;
    /** @var array<int,string> */

    public function __construct()
    {
        // Bütün faylları public diskində saxlayırıq
        $this->disk = 'public';

        $this->maxFileSizeBytes = $this->parseSizeToBytes(
            (string) Settings::get('file_manager', 'max_video_size', '10MB')
        );

        $this->allowedVideos = Settings::get('file_manager', 'allowed_videos', [
            'mp4','avi'
        ]);
    }

    public function uploadVideo(UploadedFile $file, string $dir, string $field = 'file'): string
    {
        $this->validateExtension($file, $this->allowedVideos, $field);
        $this->validateSize($file, $field);

        return $this->storeRaw($file, $dir);
    }


    /**
     * Faylı olduğu kimi saxla (public - form uploads üçün).
     */
    public function storeRaw(UploadedFile $file, string $dir): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        if ($ext === '') {
            // fallback for some environments where original extension isn't available
            $ext = strtolower($file->guessExtension() ?: 'bin');
        }

        $name = uniqid('f_', true) . '.' . $ext;

        // Ensure directory is provided
        $dir = trim($dir);
        if ($dir === '') {
            $dir = 'uploads';
        }

        // Remove 'public/' prefix if exists (disk is already set to 'public')
        $cleanDir = trim(preg_replace('#^public/?#i', '', $dir), "/ \t\n\r\0\x0B");
        if ($cleanDir === '' || $cleanDir === '.' || $cleanDir === '/') {
            $cleanDir = 'uploads';
        }

        // Use Storage facade (stream-based) instead of UploadedFile::storeAs
        // to avoid issues when getRealPath() returns false in some environments.
        $disk = Storage::disk($this->disk);

        $path = trim($cleanDir . '/' . $name, '/');

        $stream = fopen($file->getPathname(), 'r');
        if ($stream === false) {
            throw new \RuntimeException('Failed to open uploaded file stream for writing.');
        }

        try {
            $ok = $disk->put($path, $stream);

            if (!$ok) {
                throw new \RuntimeException('Storage::put returned false.');
            }

            return $path;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Fayl ölçüsünü yoxla.
     */
    private function validateSize(UploadedFile $file, string $field): void
    {
        if ($file->getSize() > $this->maxFileSizeBytes) {
            $maxMb = round($this->maxFileSizeBytes / 1024 / 1024, 1);
            $this->fail(
                __('File is too large. Max size: :size MB', ['size' => $maxMb]),
                $field
            );
        }
    }

    /**
     * Uzantını Settings-lə müqayisə et.
     *
     * @param  array<int,string>  $allowed
     */
    private function validateExtension(UploadedFile $file, array $allowed, string $field): void
    {
        $ext     = strtolower($file->getClientOriginalExtension() ?: '');
        $allowed = array_map('strtolower', $allowed);

        if (! in_array($ext, $allowed, true)) {
            $this->fail(
                __('Invalid file type. Allowed: :types', ['types' => implode(', ', $allowed)]),
                $field
            );
        }
    }

    /**
     * "10MB", "500KB" və ya sadəcə 1048576 kimi dəyərləri byte-a çevir.
     */
    private function parseSizeToBytes(string $val): int
    {
        $val = trim($val);
        if ($val === '') {
            return 10 * 1024 * 1024;
        }

        if (preg_match('/^(\d+)\s*(kb|mb|gb)?$/i', $val, $m)) {
            $num  = (int) $m[1];
            $unit = strtolower($m[2] ?? 'mb');

            return match ($unit) {
                'kb' => $num * 1024,
                'gb' => $num * 1024 * 1024 * 1024,
                default => $num * 1024 * 1024, // MB
            };
        }

        // Əgər format tanınmadısa, birbaşa byte kimi götürək
        return (int) $val;
    }

    private function fail(string $message, string $field): void
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
