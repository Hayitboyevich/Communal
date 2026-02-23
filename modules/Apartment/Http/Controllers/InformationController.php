<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Modules\Apartment\Http\Resources\ApartmentResource;
use Modules\Apartment\Http\Resources\CompanyResource;
use Modules\Apartment\Http\Resources\MonitoringBaseResource;
use Modules\Apartment\Http\Resources\MonitoringStatusResource;
use Modules\Apartment\Http\Resources\MonitoringTypeResource;
use Modules\Apartment\Http\Resources\PlaceResource;
use Modules\Apartment\Http\Resources\ViolationTypeResource;
use Modules\Apartment\Models\Apartment;
use Modules\Apartment\Models\Company;
use Modules\Apartment\Models\MonitoringBase;
use Modules\Apartment\Models\MonitoringStatus;
use Modules\Apartment\Models\MonitoringType;
use Modules\Apartment\Models\ViolationType;
use Modules\Apartment\Models\WorkType;

class InformationController extends BaseController
{
    public function place($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(PlaceResource::collection(Place::query()->where('monitoring_type_id', $id)->get()), 'Place list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function violationType($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(ViolationTypeResource::collection(ViolationType::query()->where('place_id', $id)->get()), 'Violation type list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }
    public function monitoringType($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(MonitoringTypeResource::collection(MonitoringType::all()), 'Monitoring type list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function monitoringBase($id = null): JsonResponse
    {
        try {
            return $this->sendSuccess(MonitoringBaseResource::collection(MonitoringBase::all()), 'Monitoring base list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function company($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['district_id', 'region_id', 'company_name']);
            $companies = Company::query()
                ->when(!empty($filters['district_id']), function ($query) use ($filters) {
                    $query->where('district_id', $filters['district_id']);
                })
                ->when(!empty($filters['region_id']), function ($query) use ($filters) {
                    $query->where('region_id', $filters['region_id']);
                })
                ->when(!empty($filters['company_name']), function ($query) use ($filters) {
                    $query->where('company_name', 'like', '%' . $filters['company_name'] . '%');
                })
                ->get();
            return $this->sendSuccess(CompanyResource::collection($companies), 'Company list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function apartment($id = null): JsonResponse
    {
        try {
            $apartments = Apartment::query()->where('company_id', request('company_id'))->get();
            return $this->sendSuccess(ApartmentResource::collection($apartments), 'Apartment list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function monitoringStatus($id=null): JsonResponse
    {
        try {
            return $this->sendSuccess(MonitoringStatusResource::collection(MonitoringStatus::all()), 'Monitoring Status List');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function workType($id=null): JsonResponse
    {
        try {
            $data = WorkType::all();
            return $this->sendSuccess($data, 'Work Type List');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function fines()
    {
        try {
            $inn = request('inn');

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

}
