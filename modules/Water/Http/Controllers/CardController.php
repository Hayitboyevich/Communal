<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Water\Http\Requests\CardRequest;
use Modules\Water\Http\Resources\CardResource;
use Modules\Water\Services\CardService;

class CardController extends BaseController
{
    public function __construct(protected CardService $service){}

    public function register(): JsonResponse
    {
        try {
            $data = $this->service->register(request()->all());

            return $this->sendSuccess($data, 'Ok');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function verify(): JsonResponse
    {
        try {
            $data = $this->service->verify(request()->all());
            return $this->sendSuccess($data, 'Ok');
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

    public function create(CardRequest $request): JsonResponse
    {
        try {
            $data = $this->service->create($request);
            return $this->sendSuccess(CardResource::make($data), 'Ok');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


}
