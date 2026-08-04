<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Doc\DocGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateDocCommand extends Command
{
    protected $signature = 'doc:generate';
    protected $description = 'Generate Swagger documentation from JSON files';

    public function handle(): int
    {
        try {
            $this->info('Starting Swagger documentation generation...');

            $sourcePath = $this->prepareSources();

            $generator = new DocGeneratorService($sourcePath);
            $documentation = $generator->generate();

            $this->saveDocumentation($documentation);
            $this->showSummary($documentation);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error generating documentation: ' . $e->getMessage());
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    private function prepareSources(): string
    {
        $targetDir = storage_path('app/api-docs/_sources');
        File::ensureDirectoryExists($targetDir);

        foreach (File::glob($targetDir . DIRECTORY_SEPARATOR . '*.json') ?: [] as $old) {
            File::delete($old);
        }

        $rootDocs = base_path('api-docs');
        if (File::isDirectory($rootDocs)) {
            foreach (File::glob($rootDocs . DIRECTORY_SEPARATOR . '*.json') ?: [] as $src) {
                if (!is_string($src) || $src === '' || !File::exists($src)) {
                    continue;
                }

                File::copy($src, $targetDir . DIRECTORY_SEPARATOR . basename($src));
            }
        } else {
            File::ensureDirectoryExists($rootDocs);
        }

        $modulesBase = base_path('modules');
        if (File::isDirectory($modulesBase)) {
            foreach (File::directories($modulesBase) as $moduleDir) {
                $moduleName = basename($moduleDir);
                $apiDocsDir = $moduleDir . DIRECTORY_SEPARATOR . 'api-docs';

                if (!File::isDirectory($apiDocsDir)) {
                    continue;
                }

                foreach (File::glob($apiDocsDir . DIRECTORY_SEPARATOR . '*.json') ?: [] as $src) {
                    if (!is_string($src) || $src === '' || !File::exists($src)) {
                        continue;
                    }

                    $destName = strtolower($moduleName) . '__' . basename($src);
                    File::copy($src, $targetDir . DIRECTORY_SEPARATOR . $destName);
                }
            }
        }

        return $targetDir;
    }

    protected function saveDocumentation(array $documentation): void
    {
        $path = storage_path('api-docs');

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        File::put(
            $path . '/api-docs.json',
            json_encode($documentation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $this->info('Documentation saved: ' . $path . '/api-docs.json');
    }

    protected function showSummary(array $documentation): void
    {
        $this->newLine();
        $this->info('Documentation Summary:');

        $endpoints = 0;
        $tags = count($documentation['elements'] ?? []);

        foreach ($documentation['elements'] ?? [] as $module) {
            if (!isset($module['endpoints'])) {
                continue;
            }

            if (is_array($module['endpoints']) && isset($module['endpoints'][0])) {
                $endpoints += count($module['endpoints']);
                continue;
            }

            foreach ($module['endpoints'] as $path => $methods) {
                $endpoints += is_array($methods) ? count($methods) : 0;
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Endpoints', $endpoints],
                ['Tags', $tags],
            ]
        );

        $this->newLine();
        $this->info('Generated file:');
        $this->line('- ' . storage_path('api-docs/api-docs.json'));
    }
}
