<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Water\Http\Resources\DefectResource;
use Modules\Water\Http\Resources\ProtocolTypeResource;
use Modules\Water\Models\Defect;
use Modules\Water\Models\ProtocolType;

class DefectController extends BaseController
{
    public function index(): JsonResponse
    {
        try {
            $protocolTypeId = request('id');
            $protocolType = ProtocolType::query()->findOrFail($protocolTypeId);
            return $this->sendSuccess(DefectResource::collection($protocolType->defects), 'All defects');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
