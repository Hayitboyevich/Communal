<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Water\Http\Resources\ProtocolTypeResource;
use Modules\Water\Models\ProtocolType;

class ProtocolTypeController extends BaseController
{
    public function index(): JsonResponse
    {
        try {
            return $this->sendSuccess(ProtocolTypeResource::collection(ProtocolType::all()), 'All protocol types');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
