<?php

namespace Modules\Log\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Log\Models\ActivityLog;
use Modules\Log\Models\ActivityLogChange;
use Modules\Log\Models\AdminSession;
use Modules\Log\Services\ActivityLogService;

trait Auditable
{
    private static bool $auditRunning = false;

    public static function bootAuditable(): void
    {
        $modelClass = static::class;

        $register = static function (string $event, callable $callback) use ($modelClass): void {
            if (method_exists($modelClass, $event)) {
                $modelClass::$event($callback);
            }
        };

        $register('created', static function (Model $model): void {
            self::auditSimple('created', $model);
        });

        $register('updated', static function (Model $model): void {
            self::auditUpdated($model, 'updated');
        });

        $register('deleted', static function (Model $model): void {
            self::auditSimple('deleted', $model);
        });

        $register('restored', static function (Model $model): void {
            self::auditSimple('restored', $model);
        });

        $register('forceDeleted', static function (Model $model): void {
            self::auditSimple('force_deleted', $model);
        });
    }

    private static function auditUpdated(Model $model, string $action): void
    {
        if (self::$auditRunning || !self::shouldAudit($model)) {
            return;
        }

        self::$auditRunning = true;

        try {
            $dirty = $model->getChanges();
            $original = $model->getOriginal();

            $changes = app(ActivityLogService::class)->normalizeChanges($model, $dirty, $original);

            if ($changes === []) {
                return;
            }

            app(ActivityLogService::class)->createLog($action, $model, $changes, []);
        } finally {
            self::$auditRunning = false;
        }
    }

    private static function auditSimple(string $action, Model $model): void
    {
        if (self::$auditRunning || !self::shouldAudit($model)) {
            return;
        }

        self::$auditRunning = true;

        try {
            app(ActivityLogService::class)->createLog($action, $model, [], []);
        } finally {
            self::$auditRunning = false;
        }
    }

    private static function shouldAudit(Model $model): bool
    {
        $class = $model::class;

        if ($class === ActivityLog::class || $class === ActivityLogChange::class || $class === AdminSession::class) {
            return false;
        }

        $excludeModels = (array) config('log.exclude_models', []);
        if (in_array($class, $excludeModels, true)) {
            return false;
        }

        $excludeTables = (array) config('log.exclude_tables', []);
        if (in_array($model->getTable(), $excludeTables, true)) {
            return false;
        }

        return true;
    }
}
