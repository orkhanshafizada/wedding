<?php
namespace Modules\Log\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Log\Models\ActivityLog;
use Modules\Log\Models\ActivityLogChange;

final class ActivityLogService
{
    public function createLog(
        string $action,
        ?Model $subject,
        array $changes = [],
        array $meta = []
    ): ActivityLog {
        $request = request();

        $actor = Auth::user();
        $routeName = $request?->route()?->getName();

        $log = ActivityLog::create([
            'actor_id' => $actor?->getKey(),
            'actor_type' => $actor ? $actor::class : null,
            'subject_id' => $subject?->getKey(),
            'subject_type' => $subject ? $subject::class : null,
            'action' => $action,
            'module' => $this->guessModule($subject),
            'route' => $routeName ? (string) $routeName : null,
            'url' => $request instanceof Request ? (string) $request->fullUrl() : null,
            'method' => $request instanceof Request ? (string) $request->method() : null,
            'status_code' => null,
            'ip' => $request instanceof Request ? (string) $request->ip() : null,
            'user_agent' => $request instanceof Request ? (string) $request->userAgent() : null,
            'request_id' => $this->requestId(),
            'meta' => $meta ?: null,
            'created_at' => Carbon::now(),
        ]);

        foreach ($changes as $field => $pair) {
            ActivityLogChange::create([
                'activity_log_id' => (int) $log->getKey(),
                'field' => (string) $field,
                'old_value' => $this->stringify($pair['old'] ?? null),
                'new_value' => $this->stringify($pair['new'] ?? null),
                'created_at' => Carbon::now(),
            ]);
        }

        return $log;
    }

    public function normalizeChanges(Model $model, array $dirty, array $original): array
    {
        $mask = (array) config('log.mask_fields', []);
        $maxLen = (int) config('log.max_value_length', 2000);

        $out = [];

        foreach ($dirty as $field => $newValue) {
            $oldValue = $original[$field] ?? null;

            if (in_array($field, $mask, true)) {
                $out[$field] = ['old' => '[MASKED]', 'new' => '[MASKED]'];
                continue;
            }

            $old = $this->truncate($this->stringify($oldValue), $maxLen);
            $new = $this->truncate($this->stringify($newValue), $maxLen);

            if ($old === $new) {
                continue;
            }

            $out[$field] = ['old' => $old, 'new' => $new];
        }

        return $out;
    }

    private function guessModule(?Model $subject): ?string
    {
        if (!$subject) {
            return null;
        }

        $class = $subject::class;
        if (str_starts_with($class, 'Modules\\')) {
            $parts = explode('\\', $class);
            return $parts[1] ?? null;
        }

        if (str_starts_with($class, 'App\\Models\\')) {
            return 'App';
        }

        return null;
    }

    private function requestId(): ?string
    {
        $request = request();
        $rid = $request?->headers->get('X-Request-Id');
        if ($rid) {
            return (string) $rid;
        }

        return $request?->attributes->get('request_id');
    }

    private function stringify(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function truncate(?string $value, int $maxLen): ?string
    {
        if ($value === null) {
            return null;
        }

        if (mb_strlen($value) <= $maxLen) {
            return $value;
        }

        return mb_substr($value, 0, $maxLen);
    }
}
