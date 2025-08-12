<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Apartment\Http\Requests\ClaimRequest;
use Modules\Apartment\Http\Requests\ClaimUpdateRequest;
use Modules\Apartment\Http\Resources\ClaimResource;
use Modules\Apartment\Services\ClaimService;

class ClaimController extends BaseController
{
    public function __construct(protected ClaimService $service)
    {
        parent::__construct();
    }

    public function index($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['status', 'type','category', 'region_id', 'district_id', 'id','monitoring_type']);
            $claims = $id
                ? $this->service->findById($id)
                : $this->service->all()->paginate(request('per_page', 15));

            $resource = $id
                ? ClaimResource::make($claims)
                : ClaimResource::collection($claims);

            return $this->sendSuccess(
                $resource,
                $id ? 'Claim retrieved successfully.' : 'Claims retrieved successfully.',
                $id ? null : pagination($claims)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(ClaimRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create($request);
            return $this->sendSuccess(ClaimResource::make($data), 'Claim created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function update($id, ClaimUpdateRequest $request): JsonResponse
    {
        try {
            $data = $this->service->update($id, $request);
            return $this->sendSuccess(ClaimResource::make($data),  'Protocol updated successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function count(): JsonResponse
    {
        try {
            return  $this->sendSuccess($this->service->count(), 'count');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
