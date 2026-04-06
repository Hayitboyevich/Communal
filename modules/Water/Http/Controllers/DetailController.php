<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Modules\Water\Http\Resources\DefectResource;
use Modules\Water\Http\Resources\ProtocolTypeResource;
use Modules\Water\Models\Defect;
use Modules\Water\Models\ProtocolType;

class DetailController extends BaseController
{
    public function subInfo(): JsonResponse
    {
        try {
           $pid = request('pid');

            $response = Http::withBasicAuth(
                config('water.uzwater.login'),
                config('water.uzwater.password')
            )
                ->timeout(5)
                ->post(config('water.uzwater.url'), ['pid' => $pid]);
            
            if ($response->successful()) {
                return $this->sendSuccess($response->json(), 'success');
            } else {
                return $this->sendError($response->json(), 'error');
            }

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
