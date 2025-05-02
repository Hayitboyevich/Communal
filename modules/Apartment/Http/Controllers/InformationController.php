<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Models\Place;
use App\Models\ViolationType;
use Modules\Apartment\Http\Resources\ApartmentResource;
use Modules\Apartment\Http\Resources\CompanyResource;
use Modules\Apartment\Http\Resources\MonitoringBaseResource;
use Modules\Apartment\Http\Resources\MonitoringTypeResource;
use Modules\Apartment\Http\Resources\PlaceResource;
use Modules\Apartment\Http\Resources\ViolationTypeResource;
use Modules\Apartment\Models\Apartment;
use Modules\Apartment\Models\Company;
use Modules\Apartment\Models\MonitoringBase;
use Modules\Apartment\Models\MonitoringType;

class InformationController extends BaseController
{
    public function place($id = null)
    {
        try {
            return $this->sendSuccess(PlaceResource::collection(Place::all()), 'Place list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function violationType($id = null)
    {
        try {
            return $this->sendSuccess(ViolationTypeResource::collection(ViolationType::all()), 'Violation type list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }
    public function monitoringType($id = null)
    {
        try {
            return $this->sendSuccess(MonitoringTypeResource::collection(MonitoringType::all()), 'Monitoring type list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function monitoringBase($id = null)
    {
        try {
            return $this->sendSuccess(MonitoringBaseResource::collection(MonitoringBase::all()), 'Monitoring base list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function company($id = null)
    {
        try {
            return $this->sendSuccess(CompanyResource::collection(Company::all()), 'Company list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

    public function apartment($id = null)
    {
        try {
            return $this->sendSuccess(ApartmentResource::collection(Apartment::all()), 'Apartment list');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1,$exception->getMessage());
        }
    }

}
