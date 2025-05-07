<?php

namespace Modules\Apartment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringResource extends JsonResource
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
            'status' => $this->monitoring_status_id,
            'user' => $this->user ? [
                'id' => $this->user_id,
                'full_name' => $this->user->full_name
            ] : null,
            'role' => $this->role ? [
                'id' => $this->role_id,
                'name' => $this->role->name
            ] : null,
            'monitoring_type' => $this->type ? [
                'id' => $this->monitoring_type_id,
                'name' => $this->type->name
            ] : null,
            'monitoring_base' => $this->base ? [
                'id' => $this->monitoring_base_id,
                'name' => $this->base->name
            ] : null,
            'company' => $this->company ? [
                'id' => $this->company_id,
                'name' => $this->company->name
            ] : null,
            'apartment' => $this->apartment ? [
                'id' => $this->apartment_id,
                'name' => $this->home_name
            ] : null,
            'region' => $this->region ? [
                'id' => $this->region_id,
                'name' => $this->region->name_uz
            ] : null,
            'district' => $this->district ? [
                'id' => $this->district_id,
                'name' => $this->district->name_uz
            ] : null,
            'address_commit' => $this->address_commit,
            'lat' => $this->lat,
            'long' => $this->long,
            'created_at' => $this->created_at,
        ];
    }
}
