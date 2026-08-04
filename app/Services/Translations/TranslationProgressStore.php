<?php

namespace App\Services\Translations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TranslationProgressStore
{
    private int $ttlSeconds = 3600;

    public function start(string $message, array $meta = []): string
    {
        $token = Str::uuid()->toString();

        Cache::put($this->key($token), [
            'ok' => true,
            'done' => false,
            'percent' => 0,
            'message' => $message,
            'reload' => false,
            'meta' => $meta,
        ], $this->ttlSeconds);

        return $token;
    }

    public function set(string $token, int $percent, string $message): void
    {
        $state = $this->get($token);

        if ($state === null) {
            return;
        }

        $state['percent'] = max(0, min(100, $percent));
        $state['message'] = $message;

        Cache::put($this->key($token), $state, $this->ttlSeconds);
    }

    public function putMeta(string $token, array $meta): void
    {
        $state = $this->get($token);

        if ($state === null) {
            return;
        }

        $state['meta'] = array_merge($state['meta'] ?? [], $meta);

        Cache::put($this->key($token), $state, $this->ttlSeconds);
    }

    public function done(string $token, string $message, bool $reload = true): void
    {
        $state = $this->get($token) ?? [];

        Cache::put($this->key($token), array_merge($state, [
            'ok' => true,
            'done' => true,
            'percent' => 100,
            'message' => $message,
            'reload' => $reload,
        ]), $this->ttlSeconds);
    }

    public function fail(string $token, string $message): void
    {
        $state = $this->get($token) ?? [];

        Cache::put($this->key($token), array_merge($state, [
            'ok' => false,
            'done' => true,
            'percent' => 100,
            'message' => $message,
            'reload' => false,
        ]), $this->ttlSeconds);
    }

    public function get(string $token): ?array
    {
        $value = Cache::get($this->key($token));

        return is_array($value) ? $value : null;
    }

    private function key(string $token): string
    {
        return 'translations:progress:' . $token;
    }
}
