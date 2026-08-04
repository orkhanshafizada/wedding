<?php

namespace App\Jobs\Translations;

use App\Services\Translations\TranslationProgressStore;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class SyncTranslationsJob
{
    use Dispatchable;
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly int $adminId
    ) {
    }

    public function handle(TranslationProgressStore $progressStore): void
    {
        try {
            $progressStore->set($this->token, 5, __('Scanning project files...'));
            $progressStore->set($this->token, 15, __('Collecting translation usages...'));

            Artisan::call('translations:sync');

            $progressStore->set($this->token, 90, __('Finalizing synced translation keys...'));
            $progressStore->done($this->token, __('Sync completed.'), true);
        } catch (Throwable $exception) {
            $progressStore->fail($this->token, $exception->getMessage());
        }
    }
}
