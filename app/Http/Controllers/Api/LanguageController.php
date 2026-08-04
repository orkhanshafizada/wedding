<?php

namespace App\Http\Controllers\Api;

use App\Models\Language;
use Illuminate\Http\JsonResponse;

class LanguageController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $languages = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'native_name',
                'code',
                'is_rtl',
                'status',
                'is_default_admin',
                'is_default_site',
                'is_required',
                'sort_order',
            ])
            ->map(function (Language $language): array {
                return [
                    'id' => (int) $language->id,
                    'name' => (string) $language->name,
                    'native_name' => $language->native_name !== null ? (string) $language->native_name : null,
                    'code' => (string) $language->code,
                    'is_rtl' => (bool) $language->is_rtl,
                    'is_default_admin' => (bool) $language->is_default_admin,
                    'is_default_site' => (bool) $language->is_default_site,
                    'is_required' => (bool) $language->is_required,
                    'sort_order' => (int) $language->sort_order,
                ];
            })
            ->values()
            ->all();

        return $this->response($languages);
    }
}
