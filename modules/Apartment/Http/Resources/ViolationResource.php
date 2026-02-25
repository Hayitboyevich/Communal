<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DocumentResource;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViolationResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'desc' => $this->desc,
            'deadline' => $this->deadline,
//            'images' => $this->images ? ImageResource::collection($this->images) : null,
//            'files' => $this->documents ? DocumentResource::collection($this->documents) : null,
        ];
    }
}
