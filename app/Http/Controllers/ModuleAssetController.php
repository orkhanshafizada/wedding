<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ModuleAssetController extends Controller
{
    public function __invoke(string $module, string $path): BinaryFileResponse
    {
        $module = strtolower(trim($module));
        $path = ltrim(str_replace('\\', '/', $path), '/');

        abort_if($module === '' || $path === '' || str_contains($path, '..'), 404);

        $moduleDirectory = $this->resolveModuleDirectory($module);

        abort_if($moduleDirectory === null, 404);

        $modulePublicDirectory = $this->resolvePublicDirectory($moduleDirectory);

        abort_if($modulePublicDirectory === null, 404);

        $assetPath = realpath($modulePublicDirectory . DIRECTORY_SEPARATOR . $path);

        abort_if($assetPath === false, 404);
        abort_if(! str_starts_with($assetPath, $modulePublicDirectory . DIRECTORY_SEPARATOR), 404);
        abort_if(! File::isFile($assetPath), 404);

        return response()->file($assetPath, [
            'Content-Type' => $this->mimeType($assetPath),
            'Cache-Control' => 'public, max-age=31536000',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function resolveModuleDirectory(string $module): ?string
    {
        $basePath = (string) config('modules.path', base_path('modules'));

        if (! File::isDirectory($basePath)) {
            return null;
        }

        $disabledModules = collect((array) config('modules.disabled', []));

        foreach (File::directories($basePath) as $directory) {
            $moduleFile = $directory . DIRECTORY_SEPARATOR . 'module.php';

            if (! File::exists($moduleFile)) {
                continue;
            }

            $metadata = require $moduleFile;

            if (! is_array($metadata)) {
                continue;
            }

            $directoryName = basename($directory);
            $moduleName = Arr::get($metadata, 'name', $directoryName);

            if (! is_string($moduleName) || trim($moduleName) === '') {
                $moduleName = $directoryName;
            }

            $moduleName = trim($moduleName);

            if ($disabledModules->contains($moduleName) || $disabledModules->contains($directoryName)) {
                continue;
            }

            if (strtolower($directoryName) === $module || strtolower($moduleName) === $module) {
                return $directory;
            }
        }

        return null;
    }

    private function resolvePublicDirectory(string $moduleDirectory): ?string
    {
        $exactPublicDirectory = realpath($moduleDirectory . DIRECTORY_SEPARATOR . 'public');

        if ($exactPublicDirectory !== false && File::isDirectory($exactPublicDirectory)) {
            return $exactPublicDirectory;
        }

        if (! File::isDirectory($moduleDirectory)) {
            return null;
        }

        $directories = File::directories($moduleDirectory);

        sort($directories);

        foreach ($directories as $directory) {
            if (strtolower(basename($directory)) !== 'public') {
                continue;
            }

            $realDirectory = realpath($directory);

            if ($realDirectory !== false && File::isDirectory($realDirectory)) {
                return $realDirectory;
            }
        }

        return null;
    }

    private function mimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            default => File::mimeType($path) ?: 'application/octet-stream',
        };
    }
}
