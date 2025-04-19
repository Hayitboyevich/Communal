<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
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
}
