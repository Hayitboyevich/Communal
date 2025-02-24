<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Resources\DistrictResource;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class DistrictController extends BaseController
{
    public function list(): JsonResponse
    {
        try {
            $region = Region::query()->findOrFail(request('region_id'));
            return $this->sendSuccess(DistrictResource::collection($region->districts), 'District list');

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
