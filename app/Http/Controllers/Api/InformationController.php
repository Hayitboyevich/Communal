<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Water\Http\Resources\ProtocolTypeResource;
use Modules\Water\Models\ProtocolType;

class InformationController extends BaseController
{
    public function types(): JsonResponse
    {
        try {
           return $this->sendSuccess(ProtocolTypeResource::collection(ProtocolType::all()), 'All types');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function region($id = null): JsonResponse
    {
        try {
            $regions = $id ? Region::query()->findOrFail($id) : Region::all();
            $resource = $id ? RegionResource::make($regions) : RegionResource::collection($regions);

            return $this->sendSuccess($resource, 'Region');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function district(): JsonResponse
    {
        try {
            $region = Region::query()->findOrFail(request('region_id'));
            return $this->sendSuccess(DistrictResource::collection($region->districts), 'District list');

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
