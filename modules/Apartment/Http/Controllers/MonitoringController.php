<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MonitoringCreateSecondRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Modules\Apartment\Http\Requests\MonitoringCreateRequest;
use Modules\Apartment\Http\Requests\ViolationRequest;
use Modules\Apartment\Http\Resources\MonitoringResource;
use Modules\Apartment\Services\MonitoringService;

class MonitoringController extends BaseController
{
    public function __construct(
        protected MonitoringService $service
    ){
        parent::__construct();
    }

    public function index($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['status']);
            $monitorings = $id
                ? $this->service->findById($id)
                : $this->service->getAll($this->user, $this->roleId, $filters)->paginate(request('per_page', 15));

            $resource = $id
                ? MonitoringResource::make($monitorings)
                : MonitoringResource::collection($monitorings);

            return $this->sendSuccess(
                $resource,
                $id ? 'Protocol retrieved successfully.' : 'Protocols retrieved successfully.',
                $id ? null : pagination($monitorings)
            );

        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getLine());
        }
    }

    public function count(): JsonResponse
    {
        try {
           $data = $this->service->count($this->user, $this->roleId);
           return $this->sendSuccess($data, 'Count');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(MonitoringCreateRequest $request): JsonResponse
    {
        try {
           $monitoring = $this->service->create($request);
           return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getLine());
        }
    }

    public function createSecond($id, MonitoringCreateSecondRequest $request): JsonResponse
    {
        try {

            $this->service->createSecond($id, $request);
            return $this->sendSuccess([], 'Monitoring created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createThird($id, ViolationRequest $request): JsonResponse
    {
        try {
            $this->service->createThird($id, $request);
            return $this->sendSuccess([], 'Monitoring violation successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getLine());
        }
    }

//    public function violation($id, ViolationRequest $request): JsonResponse
//    {
//        try {
//            $this->service->createThird($id, $request);
//            return $this->sendSuccess([], 'Monitoring violation successfully.');
//        }catch (\Exception $exception){
//            return $this->sendError(ErrorMessage::ERROR_1, $exception->getLine());
//        }
//    }



    public function confirm($id): JsonResponse
    {
        try {
           $monitoring = $this->service->confirm($id);
           return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring confirmed successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getLine());
        }
    }

    public function reject($id, Request $request): JsonResponse
    {
        try {
            $monitoring = $this->service->reject($id, $request);
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring confirmed successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getLine());
        }
    }


}
