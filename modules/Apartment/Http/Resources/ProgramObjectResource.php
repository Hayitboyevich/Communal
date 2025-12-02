<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DocumentResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramObjectResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'region' => $this->region ? RegionResource::make($this->region) : null,
            'district' => $this->district ? RegionResource::make($this->district) : null,
            'quarter_name' => $this->quarter_name,
            'street_name' => $this->street_name,
            'apartment_number' => $this->apartment_number,
            'created_at' => $this->created_at,
            'status' => $this->status,
        ];
    }
}
