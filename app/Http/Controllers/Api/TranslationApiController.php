<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\TranslationResource;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;

class TranslationApiController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $locale = app()->getLocale();

        $translations = Translation::query()
            ->where('locale', $locale)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->orderBy('key')
            ->get([
                'id',
                'key',
                'locale',
                'value',
                'status',
                'updated_at',
                'created_at',
            ]);

        return $this->response(
            TranslationResource::collection($translations)->resolve()
        );
    }
}
