<?php

namespace Modules\Water\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FineResource extends JsonResource
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
            'region' => $this->region ? RegionResource::make($this->region) :null,
            'district' => $this->district ? DistrictResource::make($this->district) :null,
            'protocol_article_part' => $this->protocol_article_part,
            'series' => $this->series,
            'number' => $this->number,
            'decision_series' => $this->decision_series,
            'decision_number' => $this->decision_number,
            'status_name' => $this->status_name,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'second_name' => $this->second_name,
            'document_series' => $this->document_series,
            'document_number' => $this->document_number,
            'employment_place' => $this->employment_place,
            'employment_position' => $this->employment_position,
            'main_punishment_amount' => $this->main_punishment_amount,
            'resolution_consider_info' => $this->resolution_consider_info,
            'paid_amount' => $this->paid_amount,
        ];
    }
}
