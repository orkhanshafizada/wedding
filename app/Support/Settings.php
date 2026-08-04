<?php

namespace App\Support;

use App\Models\Setting;

class Settings
{
    public static function get(string $group, string $key, $default=null) {
        $row = Setting::where(compact('group','key'))->first();
        return $row?->value ?? $default;
    }

    public static function set(string $group, string $key, $value): void {
        Setting::updateOrCreate(compact('group','key'), ['value'=>$value]);
    }

    public static function setMany(string $group, array $data): void {
        foreach ($data as $k=>$v) self::set($group,$k,$v);
    }

    // Multilang dəyərlər üçün: value = ['language_id' => 'text', ...]
    public static function getLangMap(string $group, string $key, $languages, $default=''): array {
        $cur = self::get($group,$key,[]);
        $out = [];
        foreach ($languages as $lang) {
            $id = (string)$lang->id;
            $out[$id] = $cur[$id] ?? $default;
        }
        return $out;
    }
}
