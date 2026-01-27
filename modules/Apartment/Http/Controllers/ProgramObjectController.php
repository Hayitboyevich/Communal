<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Apartment\Http\Requests\AttachObjectRequest;
use Modules\Apartment\Http\Requests\ProgramObjectRequest;
use Modules\Apartment\Http\Resources\ObjectCheckListResource;
use Modules\Apartment\Http\Resources\ProgramObjectResource;
use Modules\Apartment\Services\ProgramObjectService;

class ProgramObjectController extends BaseController
{
    public function __construct(public ProgramObjectService $service)
    {
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
                ? ProgramObjectResource::make($data)
                : ProgramObjectResource::collection($data);

            return $this->sendSuccess(
                $resource,
                $id ? 'Object retrieved successfully.' : 'Objects retrieved successfully.',
                $id ? null : pagination($data)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function checklist($id): JsonResponse
    {
        try {
            $object = $this->service->findById($id);
            return $this->sendSuccess(ObjectCheckListResource::collection($object->checklists), 'Object checklist retrieved successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(ProgramObjectRequest $request): JsonResponse
    {
        try {

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function attach(AttachObjectRequest $request): JsonResponse
    {
        try {
            $data = $this->service->attach($request);
            return $this->sendSuccess($data, 'Object attached successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
