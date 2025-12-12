<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramMonitoringListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lat' => $this->lat,
            'long' => $this->long,
            'region' => $this->object->region ? RegionResource::make($this->object->region) : null,
            'district' => $this->object->district ? DistrictResource::make($this->object->district) : null,
            'apartment_number' => $this->object->apartment_number,
            'regulation_count' => $this->regulation_count,
            'done' => 0,
            'progress' => 0,
        ];
    }
}
