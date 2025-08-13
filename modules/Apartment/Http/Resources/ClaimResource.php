<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DocumentResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'region' => $this->region ? RegionResource::make($this->region) : null,
            'district' => $this->district ? RegionResource::make($this->district) : null,
            'responsible' => $this->responsible ? UserResource::make($this->responsible) : null,
            'inspector' => $this->inspector ? UserResource::make($this->inspector) : null,
            'address' => $this->address,
            'comment' => $this->comment,
            'cadastral_number' => $this->cadastral_number,
            'full_name' => $this->full_name,
            'pin' => $this->responsible_pin,
            'status' => $this->status,
            'docs' => $this->documents ? DocumentResource::collection($this->documents) : null,
        ];
    }
}
