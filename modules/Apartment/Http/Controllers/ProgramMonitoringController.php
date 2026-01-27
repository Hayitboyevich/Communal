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

    public function index($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['work_type', 'object_id', 'street', 'quarter', 'apartment', 'region_id', 'district_id']);
            $data = $id
                ? $this->service->findById($id)
                : $this->service->getAll($filters)->orderBy('created_at', 'desc')->paginate(request('per_page', 15));

            $resource = $id
                ? ProgramMonitoringResource::make($data)
                : ProgramMonitoringResource::collection($data);

            return $this->sendSuccess(
                $resource,
                $id ? 'Monitoring retrieved successfully.' : 'Monitorings retrieved successfully.',
                $id ? null : pagination($data)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(ProgramMonitoringRequest $request): JsonResponse
    {
        try {

            $data = $this->service->create($request, $this->user, $this->roleId);
            return $this->sendSuccess(ProgramMonitoringResource::make($data), 'Monitoring created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
