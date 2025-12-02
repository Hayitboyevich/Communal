<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\ImageResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
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
        ];
    }
}
