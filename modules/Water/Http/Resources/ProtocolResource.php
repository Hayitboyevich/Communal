<?php

namespace Modules\Water\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\DocumentResource;
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
            'user_type' => $this->user_type,
            'inn' => $this->inn,
            'enterprise_name' => $this->enterprise_name,
            'pin' => $this->pin,
            'birth_date' => $this->birth_date,
            'self_government_name' => $this->self_government_name,
            'inspector_name' => $this->inspector_name,
            'participant_name' => $this->participant_name,
            'defect_information' => $this->defect_information,
            'comment' => $this->comment,
            'deadline' => $this->deadline,
            'is_finished' => $this->is_finished,
            'files' =>  DocumentResource::collection($this->documents),
            'created_at' => $this->created_at
        ];
    }
}
