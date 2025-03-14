<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Water\Services\CardService;

class CardController extends BaseController
{
    public function __construct(protected CardService $service){}

    public function register(): JsonResponse
    {
        try {
            $data = $this->service->register(request()->all());
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function verify(): JsonResponse
    {
        try {

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function cardByPhone(): JsonResponse
    {
        try {

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


}
