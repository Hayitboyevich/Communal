<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class RegionController extends BaseController
{
    public function index($id = null): JsonResponse
    {
        try {
            $regions = $id ? Region::query()->findOrFail($id) : Region::all();
            $resource = $id ? RegionResource::make($regions) : RegionResource::collection($regions);

            return $this->sendSuccess($resource, 'Region');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

}
