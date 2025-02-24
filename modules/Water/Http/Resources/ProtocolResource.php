<?php

namespace Modules\Water\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\RegionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProtocolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'region' => $this->region_id ? RegionResource::make($this->region) : null,
            'district' => $this->district_id ? DistrictResource::make($this->district) : null,
            'protocol_type' => $this->protocol_type_id ? ProtocolTypeResource::make($this->type) : null,
            'status' => $this->protocol_status_id ? ProtocolStatusResource::make($this->status) : null,
            'address' => $this->address,
            'lat' => $this->lat,
            'long' => $this->long,
            'images' => $this->images ?  ImageResource::collection($this->images) : null,
        ];
    }
}
