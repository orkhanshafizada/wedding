<?php

namespace Modules\MainPage\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\MainPage\Http\Resources\Api\MainPageSectionResource;
use Modules\MainPage\Models\MainPageSection;
use Modules\MainPage\Services\MainPageSectionResolverService;

class MainPageApiController extends BaseApiController
{
    public function __construct(
        protected readonly MainPageSectionResolverService $resolverService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $sections = MainPageSection::query()
            ->with('translations')
            ->active()
            ->ordered()
            ->get();

        foreach ($sections as $section) {
            $section->resolved_data = $this->resolverService->resolve($section, $request);
        }

        return $this->response(
            MainPageSectionResource::collection($sections)->toArray($request),
            __('Main page sections loaded successfully')
        );
    }
}
