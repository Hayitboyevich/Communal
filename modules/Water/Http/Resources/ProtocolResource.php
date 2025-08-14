<?php

namespace Modules\Water\Http\Resources;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\VideoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Water\Const\TypeList;

class ProtocolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'region' => $this->region_id ? RegionResource::make($this->region) : null,
            'district' => $this->district_id ? DistrictResource::make($this->district) : null,
            'protocol_type' => $this->protocol_type_id ? ProtocolTypeResource::make($this->protocolType) : null,
            'status' => $this->protocol_status_id ? ProtocolStatusResource::make($this->status) : null,
            'address' => $this->address,
            'description' => $this->description,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->type == TypeList::OGOH_FUQARO ? $this->fish : $this->user->full_name ?? '',
            ],
            'inspector' => $this->inspector ? [
                'id' => $this->inspector_id,
                'name' => $this->inspector->full_name ?? '',
            ] : null,
            'role' => $this->role ? [
                'id' => $this->role_id,
                'name' => $this->role->name ?? '',
            ] : null,
            'lat' => $this->lat,
            'long' => $this->long,
            'images' => $this->images ?  ImageResource::collection($this->images) : null,
            'functionary_name' => $this->functionary_name,
            'phone' => $this->phone,
            'user_type' => $this->user_type,
            'inn' => $this->inn,
            'enterprise_name' => $this->enterprise_name,
            'pin' => $this->pin,
            'additional_comment' => $this->additional_comment,
            'birth_date' => $this->birth_date,
            'self_government_name' => $this->self_government_name,
            'inspector_name' => $this->inspector_name,
            'participant_name' => $this->participant_name,
            'defect_information' => $this->defect_information,
            'comment' => $this->comment,
            'deadline' => $this->deadline,
            'is_finished' => $this->is_finished,
            'defect' => $this->defect ? DefectResource::make($this->defect) : null,
            'defect_comment' => $this->defect_comment,
            'is_administrative' => $this->is_administrative,
            'files' =>  $this->documents ? DocumentResource::collection($this->documents) : null,
            'videos' => $this->videos ?  VideoResource::collection($this->videos) : null,
            'additional_files' => collect(json_decode($this->additional_files, true))->map(function ($file) {
                return [
                    'url' => url('storage/'.$file['url']),
                ];
            }),
            'image_files' => collect(json_decode($this->image_files, true))->map(function ($file) {
                return [
                    'url' => url('storage/' . $file['url']),
                ];
            }),
            'created_at' => $this->created_at,
            'step' => $this->step,
            'type' => $this->type,
            'fine' => $this->fine ? FineResource::make($this->fine) : null,
        ];
    }
}
