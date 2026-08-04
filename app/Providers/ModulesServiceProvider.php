<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\ModuleAssetController;

class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('modules.php'), 'modules');

        foreach ($this->discoverModules() as $module) {
            foreach ($module['providers'] as $provider) {
                if (is_string($provider) && class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        }
    }

    public function boot(): void
    {
        $this->loadModuleAssetRoute();

        foreach ($this->discoverModules() as $module) {
            $this->bootModule($module);
            $this->loadApiDocsIfExists($module['path'], $module['directory_name']);
        }
    }

    private function discoverModules(): array
    {
        $modules = [];
        $basePath = (string) config('modules.path', base_path('modules'));

        if (! File::isDirectory($basePath)) {
            return $modules;
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
            $moduleName = Arr::get($metadata, 'name');

            if (! is_string($moduleName) || trim($moduleName) === '') {
                $moduleName = $directoryName;
            }

            $moduleName = trim($moduleName);

            $enabled = (bool) Arr::get($metadata, 'enabled', true);

            if ($disabledModules->contains($moduleName) || $disabledModules->contains($directoryName)) {
                $enabled = false;
            }

            if (! $enabled) {
                continue;
            }

            $providers = Arr::get($metadata, 'providers', []);

            if (! is_array($providers)) {
                $providers = [];
            }

            $modules[] = [
                'name' => $moduleName,
                'directory_name' => $directoryName,
                'path' => $directory,
                'enabled' => true,
                'providers' => array_values(array_filter($providers, fn ($provider): bool => is_string($provider) && trim($provider) !== '')),
            ];
        }

        return $modules;
    }

    private function bootModule(array $module): void
    {
        $path = $module['path'];
        $namespace = $this->moduleViewNamespace($module['directory_name']);

        $this->loadRoutesIfExists($path . '/routes/web.php');
        $this->loadAdminRoutesIfExists($path . '/routes/admin.php');
        $this->loadApiRoutesIfExists($path . '/routes/api.php');

        $migrationsPath = $this->firstExistingDir([
            $path . '/database/migrations',
            $path . '/Database/migrations',
        ]);

        if ($migrationsPath !== null) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        $viewsPath = $this->firstExistingDir([
            $path . '/resources/views',
            $path . '/Resources/views',
        ]);

        if ($viewsPath !== null) {
            $this->loadViewsFrom($viewsPath, $namespace);
        }

        $langPath = $this->firstExistingDir([
            $path . '/resources/lang',
            $path . '/Resources/lang',
        ]);

        if ($langPath !== null) {
            $this->loadTranslationsFrom($langPath, $namespace);
        }
    }

    private function loadRoutesIfExists(string $file): void
    {
        if (File::exists($file)) {
            $this->loadRoutesFrom($file);
        }
    }

    private function loadAdminRoutesIfExists(string $file): void
    {
        if (! File::exists($file)) {
            return;
        }

        Route::prefix('ayti')
            ->middleware(['web', 'auth', 'admin.access', 'admin.locale'])
            ->as('admin.')
            ->group($file);
    }

    private function loadApiRoutesIfExists(string $file): void
    {
        if (! File::exists($file)) {
            return;
        }

        Route::prefix('api/v1')
            ->middleware(['api', 'set.locale.header'])
            ->as('api.v1.')
            ->group($file);
    }

    private function moduleViewNamespace(string $name): string
    {
        return strtolower($name);
    }

    private function firstExistingDir(array $candidates): ?string
    {
        foreach ($candidates as $directory) {
            if (File::isDirectory($directory)) {
                return $directory;
            }
        }

        return null;
    }

    private function loadApiDocsIfExists(string $modulePath, string $moduleName): void
    {
        $apiDocsDirectory = $modulePath . DIRECTORY_SEPARATOR . 'api-docs';

        if (! File::isDirectory($apiDocsDirectory)) {
            return;
        }

        $jsonFiles = File::glob($apiDocsDirectory . DIRECTORY_SEPARATOR . '*.json');

        if (! is_array($jsonFiles) || $jsonFiles === []) {
            return;
        }

        $targetDirectory = storage_path('app/api-docs/modules/' . strtolower($moduleName));

        File::ensureDirectoryExists($targetDirectory);

        foreach ($jsonFiles as $source) {
            if (! is_string($source) || trim($source) === '' || ! File::exists($source)) {
                continue;
            }

            $destination = $targetDirectory . DIRECTORY_SEPARATOR . basename($source);

            $shouldCopy = ! File::exists($destination)
                || File::lastModified($source) > File::lastModified($destination);

            if ($shouldCopy) {
                File::copy($source, $destination);
            }
        }
    }

    private function loadModuleAssetRoute(): void
    {
        Route::middleware('web')
            ->get('module-assets/{module}/{path}', ModuleAssetController::class)
            ->where('path', '.*')
            ->name('modules.assets');
    }
}
