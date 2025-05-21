<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DocumentResource;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegulationResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'place' => $this->place ? PlaceResource::make($this->place): null,
            'violation_type' => $this->violationType ? ViolationTypeResource::make($this->violationType): null,
            'user_type' => $this->user_type,
            'pin' => $this->pin,
            'organization_name' => $this->organization_name,
            'company' => $this->company ? CompanyResource::make($this->company) : null,
            'inn' => $this->inn,
            'birth_date' => $this->birth_date,
            'fish' => $this->fish,
            'phone' => $this->phone,
            'images' => $this->images ? ImageResource::collection($this->images) : null,
        ];
    }
}
