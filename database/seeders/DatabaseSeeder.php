<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            SettingSeeder::class,
            PermissionSeeder::class,
        ]);

        foreach ($this->moduleSeederClasses() as $seederClass) {
            $this->call($seederClass);
        }

        $this->call([
            UserSeeder::class,
        ]);
    }

    private function moduleSeederClasses(): array
    {
        $classes = [];

        $defaultModulesPath = File::isDirectory(base_path('Modules'))
            ? base_path('Modules')
            : base_path('modules');

        $modulesBasePath = (string) config('modules.path', $defaultModulesPath);

        if (! File::isDirectory($modulesBasePath)) {
            return [];
        }

        $disabledModules = collect((array) config('modules.disabled', []));

        foreach (File::directories($modulesBasePath) as $modulePath) {
            $moduleFile = $modulePath . DIRECTORY_SEPARATOR . 'module.php';

            if (! File::exists($moduleFile)) {
                continue;
            }

            $metadata = require $moduleFile;

            if (! is_array($metadata)) {
                continue;
            }

            $directoryName = basename($modulePath);
            $moduleName = Arr::get($metadata, 'name');

            if (! is_string($moduleName) || trim($moduleName) === '') {
                $moduleName = $directoryName;
            }

            $moduleName = trim($moduleName);
            $enabled = (bool) Arr::get($metadata, 'enabled', true);

            if ($disabledModules->contains($moduleName) || $disabledModules->contains($directoryName) || ! $enabled) {
                continue;
            }

            $seedersDirectory = $this->firstExistingDir([
                $modulePath . '/database/Seeders',
                $modulePath . '/database/seeders',
                $modulePath . '/Database/Seeders',
                $modulePath . '/Database/seeders',
            ]);

            if ($seedersDirectory === null) {
                continue;
            }

            $namespace = 'Modules\\' . $directoryName . '\\Database\\Seeders\\';

            $files = collect(File::files($seedersDirectory))
                ->filter(fn ($file): bool => $file->getExtension() === 'php')
                ->sortBy(fn ($file): string => $file->getFilename())
                ->values();

            foreach ($files as $file) {
                $class = $namespace . $file->getFilenameWithoutExtension();

                if (! class_exists($class)) {
                    require_once $file->getPathname();
                }

                if (! class_exists($class)) {
                    continue;
                }

                if (! $this->isRunnableSeeder($class)) {
                    continue;
                }

                $classes[] = $class;
            }
        }

        return array_values(array_unique($classes));
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

    private function isRunnableSeeder(string $class): bool
    {
        if (! is_subclass_of($class, Seeder::class)) {
            return false;
        }

        $reflection = new ReflectionClass($class);

        if ($reflection->isAbstract()) {
            return false;
        }

        if ($reflection->getName() === self::class) {
            return false;
        }

        return method_exists($class, 'run');
    }
}
