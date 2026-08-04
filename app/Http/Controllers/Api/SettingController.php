<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Setting\PublicSettingResource;
use App\Models\Language;
use App\Services\SettingService;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SettingController extends BaseApiController
{
    public function index(Request $request, SettingService $settingService): JsonResponse
    {
        $activeLanguages = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $defaultLanguageId = (int) Settings::get('system', 'default_language_id', 1);

        $language = $this->resolveLanguage($request, $activeLanguages, $defaultLanguageId);

        $payload = $settingService->publicSettings($language, $activeLanguages);

        return $this->response(new PublicSettingResource($payload));
    }

    private function resolveLanguage(Request $request, $activeLanguages, int $defaultLanguageId): Language
    {
        $lang = $request->query('lang');

        if (is_numeric($lang)) {
            $found = $activeLanguages->firstWhere('id', (int) $lang);
            if ($found instanceof Language) {
                return $found;
            }
        }

        if (is_string($lang) && $lang !== '') {
            $found = $activeLanguages->first(function (Language $language) use ($lang): bool {
                $code = (string) ($language->code ?? '');
                return $code !== '' && mb_strtolower($code) === mb_strtolower($lang);
            });

            if ($found instanceof Language) {
                return $found;
            }
        }

        $found = $activeLanguages->firstWhere('id', $defaultLanguageId);

        return $found instanceof Language
            ? $found
            : ($activeLanguages->first() ?? Language::query()->findOrFail($defaultLanguageId));
    }
}
