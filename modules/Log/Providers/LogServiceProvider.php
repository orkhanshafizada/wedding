<?php
namespace Modules\Log\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Log\Http\Middleware\AdminSessionHeartbeat;
use Modules\Log\Services\ActivityLogService;

final class LogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/log.php', 'log');
    }

    public function boot(): void
    {
        Route::pushMiddlewareToGroup('web', AdminSessionHeartbeat::class);

        //$this->registerWildcardAudit();
    }

    private function registerWildcardAudit(): void
    {
        if ((bool) config('log.audit_all_models', true) !== true) {
            return;
        }

        Event::listen('eloquent.updated: *', function (string $event, array $data): void {
            $model = $data[0] ?? null;
            if (!$model instanceof Model) {
                return;
            }

            if (!$this->shouldAudit($model)) {
                return;
            }

            $dirty = $model->getChanges();
            $original = $model->getOriginal();

            $changes = app(ActivityLogService::class)->normalizeChanges($model, $dirty, $original);
            if ($changes === []) {
                return;
            }

            app(ActivityLogService::class)->createLog('updated', $model, $changes, []);
        });

        Event::listen('eloquent.created: *', function (string $event, array $data): void {
            $model = $data[0] ?? null;
            if (!$model instanceof Model) {
                return;
            }

            if (!$this->shouldAudit($model)) {
                return;
            }

            app(ActivityLogService::class)->createLog('created', $model, [], []);
        });

        Event::listen('eloquent.deleted: *', function (string $event, array $data): void {
            $model = $data[0] ?? null;
            if (!$model instanceof Model) {
                return;
            }

            if (!$this->shouldAudit($model)) {
                return;
            }

            app(ActivityLogService::class)->createLog('deleted', $model, [], []);
        });

        Event::listen('eloquent.restored: *', function (string $event, array $data): void {
            $model = $data[0] ?? null;
            if (!$model instanceof Model) {
                return;
            }

            if (!$this->shouldAudit($model)) {
                return;
            }

            app(ActivityLogService::class)->createLog('restored', $model, [], []);
        });
    }

    private function shouldAudit(Model $model): bool
    {
        $excludeModels = (array) config('log.exclude_models', []);
        if (in_array($model::class, $excludeModels, true)) {
            return false;
        }

        $table = $model->getTable();
        $excludeTables = (array) config('log.exclude_tables', []);
        if (in_array($table, $excludeTables, true)) {
            return false;
        }

        $includeNamespaces = (array) config('log.include_namespaces', []);
        $class = $model::class;

        foreach ($includeNamespaces as $ns) {
            if ($ns && str_starts_with($class, $ns)) {
                return true;
            }
        }

        return false;
    }
}
