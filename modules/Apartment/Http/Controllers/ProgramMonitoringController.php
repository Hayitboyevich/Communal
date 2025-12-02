<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Apartment\Http\Requests\ProgramMonitoringRequest;
use Modules\Apartment\Http\Resources\ProgramMonitoringResource;
use Modules\Apartment\Services\ProgramMonitoringService;

class ProgramMonitoringController extends BaseController
{
    public function __construct(public ProgramMonitoringService $service){
        parent::__construct();
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->service->getAll()->paginate(request('per_page', 15));
            return $this->sendSuccess(ProgramMonitoringResource::collection($data), 'Program Monitoring List', pagination($data));
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(ProgramMonitoringRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create($request);
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
