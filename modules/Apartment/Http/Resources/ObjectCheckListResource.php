<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DocumentResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Apartment\Models\WorkType;

class ObjectCheckListResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'program'  => $this->program ? ProgramResource::make($this->program) : null,
            'checklist' => $this->checklist ? CheckListResource::make($this->checklist) : null,
            'plan' => $this->plan,
            'unit' => $this->unit,
            'status' => $this->status,
            'done' => $this->regulation ? $this->regulation->done : null,
            'progress' => $this->regulation ? $this->regulation->progress : null,
            'all' => $this->regulation ? $this->regulation->all : null,
            'need_repair' => $this->regulation ? $this->regulation->need_repair : null,
            'extra' => $this->regulation ? $this->regulation->extra : null,
        ];
    }
}
