<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Modules\Apartment\Http\Resources\MonitoringStatusResource;
use Modules\Apartment\Models\MonitoringStatus;

class MonitoringStatusController extends BaseController
{
    public function index($id=null)
    {
        try {
            return $this->sendSuccess(MonitoringStatusResource::collection(MonitoringStatus::all()), 'Monitoring Status List');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
