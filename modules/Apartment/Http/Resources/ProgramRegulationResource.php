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
use Modules\Apartment\Models\ProgramRegulation;

class ProgramRegulationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'checklist' => $this->checklist ? CheckListResource::make($this->checklist) : null,
            'program' => $this->program ? ProgramResource::make($this->program) : null,
            'plan' => $this->plan,
            'all' => $this->all,
            'need_repair' => $this->need_repair,
            'done' => $this->done,
            'unit' => $this->objectChecklist->unit,
            'progress' => $this->progress,
            'extra' => $this->extra,
            'images' => $this->images ? ImageResource::collection($this->images) : null,
        ];
    }
}
