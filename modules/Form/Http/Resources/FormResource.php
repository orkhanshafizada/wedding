<?php

namespace Modules\Form\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'menu_id' => $this->id,
            'form_text' => $this->whenLoaded('formText', function () {
                return new FormTextResource($this->formText);
            }),
            'labels' => FormLabelResource::collection($this->whenLoaded('formLabels')),
        ];
    }
}
