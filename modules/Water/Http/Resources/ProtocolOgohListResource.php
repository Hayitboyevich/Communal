<?php

namespace Modules\Water\Http\Resources;

use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProtocolOgohListResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->fish,
            'phone_number' => $this->phone_number,
            'appealSphere' => $this->protocolType ? $this->protocolType->name : null,
            'content' => $this->description,
            'status' => 10,
            'statusId' => $this->protocol_status_id,
            'statusText' => $this->status ? $this->status->name : null,
            'province' => $this->region ? $this->region->name_uz : null,
            'region' => $this->district ? $this->district->name_uz : null,
            'latitude' => $this->lat,
            'longitude' => $this->long,
            'files' => $this->images ?  $this->images->map(function ($image) {
                return Storage::disk('public')->url($image->url);
            }) : null,
            'address' => $this->address,
            'created_at' => $this->created_at,
        ];
    }
}
