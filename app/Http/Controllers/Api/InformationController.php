<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MonitoringMyHomeRequest;
use App\Http\Requests\ProtocolOgohRequest;
use App\Http\Requests\ProtocolWaterRequest;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Http\Resources\MonitoringResource;
use Modules\Apartment\Http\Resources\MonitoringTypeResource;
use Modules\Apartment\Models\MonitoringType;
use Modules\Apartment\Services\MonitoringService;
use Modules\Water\Http\Requests\FineUpdateRequest;
use Modules\Water\Http\Resources\ProtocolOgohListResource;
use Modules\Water\Http\Resources\ProtocolResource;
use Modules\Water\Http\Resources\ProtocolStatusResource;
use Modules\Water\Http\Resources\ProtocolTypeResource;
use Modules\Water\Models\ProtocolStatus;
use Modules\Water\Models\ProtocolType;
use Modules\Water\Services\DecisionService;
use Modules\Water\Services\ProtocolService;

class InformationController extends BaseController
{

    public function __construct(
        protected ProtocolService $service,
        protected MonitoringService $monitoringService,
        protected DecisionService $decisionService,
    ){
        parent::__construct();
    }
    public function types(): JsonResponse
    {
        try {
           return $this->sendSuccess(ProtocolTypeResource::collection(ProtocolType::all()), 'All types');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function region($id = null): JsonResponse
    {
        try {
            $regions = $id ? Region::query()->findOrFail($id) : Region::all();
            $resource = $id ? RegionResource::make($regions) : RegionResource::collection($regions);

            return $this->sendSuccess($resource, 'Region');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function district(): JsonResponse
    {
        try {
            $region = Region::query()->findOrFail(request('region_id'));
            return $this->sendSuccess(DistrictResource::collection($region->districts), 'District list');

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function protocolCreate(ProtocolOgohRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $protocol = $this->service->create($request->except('images', 'region', 'district'));
            $this->service->saveImages($protocol, $request['images']);
            DB::commit();
            return response()->json([
                "status" => "success",
                "data" => "Appeal successfully send. As soon as answer."
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function protocolWater(ProtocolWaterRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $protocol = $this->service->create($request->except('images', 'region', 'district', 'files'));
            $this->service->saveImages($protocol, $request['images']);
            $this->service->saveFiles($protocol, $request['files']);
            DB::commit();
            return response()->json([
                "status" => "success",
                "data" => []
            ]);
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function getProtocol($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['status', 'user_id', 'protocol_number', 'district_id', 'region_id', 'protocol_type', 'type', 'attach', 'category']);
            $protocols = $id
                ? $this->service->findById($id)
                : $this->service->getAll($this->user, UserRoleEnum::OGOH->value,$filters)->paginate(request('per_page', 15));


            $resource = $id
                ? ProtocolOgohListResource::make($protocols)
                : ProtocolOgohListResource::collection($protocols);

            return $this->sendSuccess(
                $resource,
                $id ? 'Protocol retrieved successfully.' : 'Protocols retrieved successfully.',
                $id ? null : pagination($protocols)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }

    }

    public function protocolHistory($id): JsonResponse
    {
        try {
            return $this->sendSuccess($this->service->history($id), 'Object History');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function monitoringCreate(MonitoringMyHomeRequest $request): JsonResponse
    {
        try {
            $monitoring = $this->monitoringService->createBasic($request);
            return $this->sendSuccess(MonitoringResource::make($monitoring), 'Monitoring created successfully.');
        }catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function monitoringType(): JsonResponse
    {
        try {
            $types = MonitoringType::all();
            return $this->sendSuccess(MonitoringTypeResource::collection($types), 'All types');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function protocolStatus($id): JsonResponse
    {
        try {
            return $this->sendSuccess(ProtocolStatusResource::collection(ProtocolStatus::all()), 'Protocol status fetched successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function fineUpdate(FineUpdateRequest $request): JsonResponse
    {
        try {
           $this->decisionService->update($request);
           return $this->sendSuccess(true, 'Updated successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
