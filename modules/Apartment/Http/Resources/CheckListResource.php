<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DocumentResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Apartment\Models\WorkType;

class CheckListResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name'  => $this->name,
            'work_type' => $this->workType ? WorkTypeResource::make($this->workType) : null,
        ];
    }
}
