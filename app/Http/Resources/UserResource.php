<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'middle_name' => $this->middle_name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'region' => $this->region ? RegionResource::make($this->region) : null,
            'district' => $this->district ? DistrictResource::make($this->district) : null,
        ];
    }
}
