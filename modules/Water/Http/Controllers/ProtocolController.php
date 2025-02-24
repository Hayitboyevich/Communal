<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Water\Http\Requests\ProtocolFirstStepRequest;
use Modules\Water\Http\Requests\ProtocolSecondStepRequest;
use Modules\Water\Http\Requests\ProtocolThirdStepRequest;
use Modules\Water\Http\Resources\ProtocolResource;
use Modules\Water\Services\ProtocolService;

class ProtocolController extends BaseController
{

    public function __construct(
        protected ProtocolService $service
    ){}

    public function index($id = null): JsonResponse
    {
        try {
            $protocols = $id ? $this->service->findById($id) : $this->service->getAll();
            $resource = $id ? ProtocolResource::make($protocols) : ProtocolResource::collection($protocols);
            return $this->sendSuccess($resource, 'Protocols retrieved successfully.');

        } catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createFirst(ProtocolFirstStepRequest $request): JsonResponse
    {
        try {
            $protocol = $this->service->createFirst($request->except('images'));
            $this->service->saveImages($protocol, $request['images']);
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createSecond(?int $id, ProtocolSecondStepRequest $request): JsonResponse
    {
        try {
            $protocol = $this->service->createSecond($id, $request->except('file'));
            $this->service->saveFiles($protocol, $request['file']);
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createThird(?int $id, ProtocolThirdStepRequest $request): JsonResponse
    {
        try {
            $protocol = $this->service->createThird($id, $request->validated());
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


}
