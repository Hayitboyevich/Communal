<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LetterResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'monitoring_id' => $this->monitoring_id,
            'regulation_id' => $this->regulation_id,
            'status' => $this->status,
            'address' => $this->address,
            'fish' => $this->fish,
            'inspector' => $this->inspector ? [
                'id' => $this->inspector->id,
                'name' => $this->inspector->name,
                'surname' => $this->inspector->surname,
                'middle_name' => $this->inspector->middle_name,
            ] : null,
            'region' => $this->region ? RegionResource::make($this->region) : null,
            'district' => $this->district ? DistrictResource::make($this->district) : null,
        ];
    }
}
