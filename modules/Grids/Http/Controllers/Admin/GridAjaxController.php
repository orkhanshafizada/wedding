<?php

namespace Modules\Grids\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Grids\Http\Resources\Admin\GridRelatedVariationSearchResource;
use Modules\Product\Models\Variation\ProductVariation;

class GridAjaxController extends Controller
{
    public function relatedProducts(Request $request): AnonymousResourceCollection
    {
        $query = trim((string) $request->get('q', ''));
        $limit = (int) $request->get('limit', 20);
        $limit = $limit > 0 ? min($limit, 50) : 20;

        $variationIdsQuery = ProductVariation::query()
            ->select('product_variations.id');

        if ($query !== '') {
            $variationIdsQuery->where(function ($builder) use ($query) {
                $builder
                    ->where('product_variations.sku', 'like', '%' . $query . '%')
                    ->orWhere('product_variations.model', 'like', '%' . $query . '%')
                    ->orWhereHas('translations', function ($translationQuery) use ($query) {
                        $translationQuery
                            ->where('name', 'like', '%' . $query . '%')
                            ->orWhere('slug', 'like', '%' . $query . '%');
                    })
                    ->orWhereIn('product_variations.product_id', function ($subQuery) use ($query) {
                        $subQuery
                            ->select('pv.product_id')
                            ->from('product_variations as pv')
                            ->join(
                                'product_variation_translations as pvt',
                                'pvt.product_variation_id',
                                '=',
                                'pv.id'
                            )
                            ->where(function ($translationQuery) use ($query) {
                                $translationQuery
                                    ->where('pvt.name', 'like', '%' . $query . '%')
                                    ->orWhere('pvt.slug', 'like', '%' . $query . '%');
                            });
                    });
            });
        }

        $variationIds = $variationIdsQuery
            ->orderByDesc('product_variations.id')
            ->limit($limit)
            ->pluck('product_variations.id')
            ->all();

        if ($variationIds === []) {
            return GridRelatedVariationSearchResource::collection(collect());
        }

        $variations = ProductVariation::query()
            ->select([
                'id',
                'product_id',
                'sku',
                'model',
            ])
            ->whereIn('id', $variationIds)
            ->with([
                'translations' => function ($translationQuery) {
                    $translationQuery
                        ->select([
                            'id',
                            'product_variation_id',
                            'language_id',
                            'name',
                            'slug',
                        ])
                        ->orderBy('language_id');
                },
                'product.variations' => function ($variationQuery) {
                    $variationQuery
                        ->select([
                            'id',
                            'product_id',
                            'sku',
                            'model',
                        ])
                        ->with([
                            'translations' => function ($translationQuery) {
                                $translationQuery
                                    ->select([
                                        'id',
                                        'product_variation_id',
                                        'language_id',
                                        'name',
                                        'slug',
                                    ])
                                    ->orderBy('language_id');
                            },
                        ])
                        ->orderBy('id');
                },
                'media' => function ($mediaQuery) {
                    $mediaQuery
                        ->select([
                            'id',
                            'product_variation_id',
                            'path',
                            'sort_order',
                            'is_main',
                        ])
                        ->orderByDesc('is_main')
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->get()
            ->sortBy(function ($variation) use ($variationIds) {
                return array_search((int) $variation->id, $variationIds, true);
            })
            ->values();

        $rows = [];

        foreach ($variations as $variation) {
            $variationName = $this->resolveVariationName($variation);
            $productName = $this->resolveProductName($variation);
            $mainMedia = $variation->media->firstWhere('is_main', true) ?: $variation->media->first();
            $imageUrl = (string) ($mainMedia?->url ?? '');

            $rows[] = [
                'type' => 'variation',
                'id' => (int) $variation->id,
                'product_id' => (int) $variation->product_id,
                'text' => $productName !== '' ? ($productName . ' — ' . $variationName) : $variationName,
                'subtitle' => 'Variation',
                'image_url' => $imageUrl,
            ];
        }

        return GridRelatedVariationSearchResource::collection(collect($rows)->values());
    }

    private function resolveVariationName(ProductVariation $variation): string
    {
        foreach ($variation->translations ?? [] as $translation) {
            $name = trim((string) ($translation->name ?? ''));

            if ($name !== '') {
                return $name;
            }
        }

        $sku = trim((string) ($variation->sku ?? ''));
        if ($sku !== '') {
            return $sku;
        }

        $model = trim((string) ($variation->model ?? ''));
        if ($model !== '') {
            return $model;
        }

        return '#' . (int) $variation->id;
    }

    private function resolveProductName(ProductVariation $variation): string
    {
        foreach ($variation->product?->variations ?? [] as $productVariation) {
            foreach ($productVariation->translations ?? [] as $translation) {
                $name = trim((string) ($translation->name ?? ''));

                if ($name !== '') {
                    return $name;
                }
            }
        }

        return '#' . (int) $variation->product_id;
    }
}
