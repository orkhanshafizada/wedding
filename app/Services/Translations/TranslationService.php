<?php

namespace App\Services\Translations;

use App\Enums\TranslationStatus;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TranslationService
{
    public function paginateGroups(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $search = trim((string) ($filters['q'] ?? ''));
        $locale = trim((string) ($filters['locale'] ?? ''));

        $query = Translation::query()
            ->selectRaw('MAX(id) as id')
            ->selectRaw('`key`')
            ->selectRaw('MAX(updated_at) as updated_at')
            ->selectRaw('MAX(created_at) as created_at')
            ->selectRaw('COUNT(*) as locale_count')
            ->selectRaw('SUM(CASE WHEN value IS NOT NULL AND TRIM(value) <> "" THEN 1 ELSE 0 END) as translated_count')
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('key', 'like', '%' . $search . '%')
                        ->orWhereExists(function ($existsQuery) use ($search) {
                            $existsQuery
                                ->selectRaw('1')
                                ->from('translations as search_translations')
                                ->whereColumn('search_translations.key', 'translations.key')
                                ->where('search_translations.value', 'like', '%' . $search . '%');
                        });
                });
            })
            ->groupBy('key')
            ->orderByDesc('id');

        $paginator = $query->paginate($perPage)->withQueryString();

        $keys = collect($paginator->items())
            ->pluck('key')
            ->filter()
            ->values()
            ->all();

        $previewMap = [];

        if ($locale !== '' && !empty($keys)) {
            $previewMap = Translation::query()
                ->whereIn('key', $keys)
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->all();
        }

        foreach ($paginator->items() as $item) {
            $item->preview_value = $previewMap[$item->key] ?? null;
        }

        return $paginator;
    }

    public function getFormData(Translation $translation): array
    {
        $languages = Language::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'code']);

        $group = Translation::query()
            ->where('key', $translation->key)
            ->get()
            ->keyBy('locale');

        return [
            'languages' => $languages,
            'group' => $group,
        ];
    }

    public function createGroup(array $data, int $adminId): Translation
    {
        return DB::transaction(function () use ($data, $adminId) {
            $key = trim((string) $data['key']);
            $languages = Language::active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['code']);

            $firstTranslation = null;

            foreach ($languages as $language) {
                $value = $data['translations'][$language->code]['value'] ?? null;

                $translation = new Translation();
                $translation->key = $key;
                $translation->locale = (string) $language->code;
                $translation->value = is_string($value) ? $value : null;
                $translation->status = TranslationStatus::fromValue($translation->value)->value;
                $translation->updated_by = $adminId;
                $translation->save();

                if ($firstTranslation === null) {
                    $firstTranslation = $translation;
                }
            }

            return $firstTranslation?->fresh();
        });
    }

    public function updateGroup(Translation $translation, array $data, int $adminId): Translation
    {
        return DB::transaction(function () use ($translation, $data, $adminId) {
            $oldKey = (string) $translation->key;
            $newKey = trim((string) $data['key']);

            $languages = Language::active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['code']);

            $existing = Translation::query()
                ->where('key', $oldKey)
                ->get()
                ->keyBy('locale');

            $firstTranslation = null;

            foreach ($languages as $language) {
                $locale = (string) $language->code;
                $value = $data['translations'][$locale]['value'] ?? null;

                /** @var Translation|null $row */
                $row = $existing->get($locale);

                if ($row === null) {
                    $row = new Translation();
                    $row->locale = $locale;
                }

                $row->key = $newKey;
                $row->value = is_string($value) ? $value : null;
                $row->status = TranslationStatus::fromValue($row->value)->value;
                $row->updated_by = $adminId;
                $row->save();

                if ($firstTranslation === null) {
                    $firstTranslation = $row;
                }
            }

            Translation::query()
                ->where('key', $oldKey)
                ->whereNotIn('locale', $languages->pluck('code')->all())
                ->delete();

            return $firstTranslation?->fresh();
        });
    }

    public function deleteGroup(Translation $translation): void
    {
        DB::transaction(function () use ($translation) {
            Translation::query()
                ->where('key', $translation->key)
                ->delete();
        });
    }

    public function localeStats(string $locale): array
    {
        $total = Translation::query()->where('locale', $locale)->count();
        $translated = Translation::query()
            ->where('locale', $locale)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();

        return [
            'total' => $total,
            'translated' => $translated,
            'draft' => max(0, $total - $translated),
            'completion_rate' => $total > 0 ? (int) round(($translated / $total) * 100) : 0,
        ];
    }

    public function findGroupRows(string $key): Collection
    {
        return Translation::query()
            ->where('key', $key)
            ->orderBy('locale')
            ->get();
    }
}
