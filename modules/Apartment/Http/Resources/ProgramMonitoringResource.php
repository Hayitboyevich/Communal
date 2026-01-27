<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramMonitoringResource extends JsonResource
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
            'object_id' => $this->object->id,
            'quarter_name' => $this->object->quarter_name,
            'street_name' => $this->object->street_name,
            'regulation_count' => $this->regulations()->count(),
            'done' => $this->regulations->filter(fn($regulation) => $regulation->plan === $regulation->done)->count(),
            'progress' => $this->regulations->filter(fn($regulation) => $regulation->plan !== $regulation->done)->count(),
            'created_at' => $this->created_at,
            'user' => $this->user
                ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'middle_name' => $this->user->middle_name,
                    'surname' => $this->user->surname,
                ]
                :
                null,
            'role' => $this->role ? RoleResource::make($this->role) : null,
            'images' => $this->images ? ImageResource::collection($this->images) : null,
            'regulations' => $this->regulations ? ProgramRegulationResource::collection($this->regulations) : null,
        ];
    }
}
