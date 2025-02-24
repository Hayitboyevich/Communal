<?php

namespace Modules\Water\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Water\Http\Resources\ProtocolStatusResource;
use Modules\Water\Models\ProtocolStatus;

class ProtocolStatusController extends BaseController
{
    public function index(): JsonResponse
    {
        try {
            return $this->sendSuccess(ProtocolStatusResource::collection(ProtocolStatus::all()), 'Protocol status fetched successfully.');
        }catch (\Exception $exception){
            return $this->sendError('Xatolik yuz berdi', $exception->getMessage());
        }
    }
}
