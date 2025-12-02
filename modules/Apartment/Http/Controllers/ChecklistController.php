<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Models\District;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Modules\Apartment\Contracts\ChecklistRepositoryInterface;
use Modules\Apartment\Http\Requests\ChecklistRequest;
use Modules\Apartment\Http\Requests\ClaimRequest;
use Modules\Apartment\Http\Requests\ClaimUpdateRequest;
use Modules\Apartment\Http\Resources\CheckListResource;
use Modules\Apartment\Http\Resources\ClaimResource;
use Modules\Apartment\Services\ChecklistService;
use Modules\Apartment\Services\ClaimService;

class ChecklistController extends BaseController
{
    public function __construct(public ChecklistService $service)
    {
        parent::__construct();
    }

    public function index($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['work_type', 'object_id']);
            $data = $id
                ? $this->service->findById($id)
                : $this->service->getAll($filters)->orderBy('created_at', 'desc')->paginate(request('per_page', 15));

            $resource = $id
                ? CheckListResource::make($data)
                : CheckListResource::collection($data);

            return $this->sendSuccess(
                $resource,
                $id ? 'Checklist retrieved successfully.' : 'Checklists retrieved successfully.',
                $id ? null : pagination($data)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function create(ChecklistRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create($request);
            return $this->sendSuccess($data, 'Checklist created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

}
