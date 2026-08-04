<?php

namespace Modules\Gallery\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GalleryAlbumItemCollection extends ResourceCollection
{
    public $collects = GalleryAlbumItemResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection
            ->map(function ($resource) use ($request) {
                return $resource->resolve($request);
            })
            ->values()
            ->all();
    }
}
