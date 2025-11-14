<?php

namespace Modules\Apartment\Http\Resources;

use App\Http\Resources\DocumentResource;
use App\Http\Resources\ImageResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Apartment\Const\MonitoringHistoryType;
use Modules\Apartment\Models\MonitoringStatus;
use Modules\Water\Http\Resources\FineResource;

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
            'step' => $this->step,
            'is_administrative' => $this->is_administrative,
            'send_court' => $this->send_court,
            'send_mib' => $this->send_mib,
            'send_chora' => $this->send_chora,
            'status' => $this->status ? [
               'id' => $this->monitoring_status_id,
               'name' => $this->status->name,
            ] : null,
            'user' => $this->user ? [
                'id' => $this->user_id,
                'name' => $this->user->full_name
            ] : null,
            'role' => $this->role ? [
                'id' => $this->role_id,
                'name' => $this->role->name
            ] : null,
            'monitoring_type' => $this->monitoringType ? [
                'id' => $this->monitoring_type_id,
                'name' => $this->monitoringType->name ?? null
            ] : null,
            'monitoring_base' => $this->base ? [
                'id' => $this->monitoring_base_id,
                'name' => $this->base->name
            ] : null,
            'company' => $this->company ? [
                'id' => $this->company_id,
                'name' => $this->company->company_name
            ] : null,
            'apartment' => $this->apartment ? [
                'id' => $this->apartment_id,
                'name' => $this->apartment->home_name
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
            'bsk_type' => $this->bsk_type,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'additional_comment' => $this->additional_comment,
            'additional_files' => collect(json_decode($this->additional_files, true))->map(function ($file) {
                return [
                    'url' => url('storage/'.$file['url']),
                ];
            }),
            'type' => $this->type,
            'regulations' => $this->regulation ? RegulationResource::make($this->regulation) : null,
            'violations' => $this->violation ? ViolationResource::make($this->violation) : null,
            'images' => $this->images ? ImageResource::collection($this->images) : null,
            'docs' => $this->documents ? DocumentResource::collection($this->documents) : null,
            'fine' => $this->fine ? FineResource::make($this->fine) : null,
            'history' => $this->histories ? $this->histories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'comment' => $history->content->comment,
                    'user' => $history->content->user ? User::query()->find($history->content->user, ['name', 'surname', 'middle_name']) : null,
                    'role' => $history->content->role ? Role::query()->find($history->content->role, ['name', 'description']) : null,
                    'status' => $history->content->status ? MonitoringStatus::query()->find($history->content->status, ['id', 'name']) : null,
                    'type' => $history->type,
                    'files' => $history->documents ? DocumentResource::collection($history->documents): null,
                    'images' =>$history->images ? ImageResource::collection($history->images): null,
                    'is_change' => $history->type ? MonitoringHistoryType::getLabel($history->type) : null,
                    'created_at' => $history->created_at,
                ];
            })->sortByDesc('created_at')->values() : null
        ];
    }
}
