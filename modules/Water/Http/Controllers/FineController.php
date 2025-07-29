<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Water\Http\Requests\FineCreateRequest;
use Modules\Water\Services\DecisionService;

class FineController extends BaseController
{

    public function __construct(protected DecisionService  $service){
        parent::__construct();
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $data = $this->service->search($request->series, $request->number);
            return $this->sendSuccess($data->data, 'success', $data->meta);
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(FineCreateRequest $request): JsonResponse
    {
        try {
            $this->service->create($request);
            return $this->sendSuccess(true, 'success');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
