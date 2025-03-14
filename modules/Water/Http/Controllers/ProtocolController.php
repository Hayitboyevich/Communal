<?php

namespace Modules\Water\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
            $protocols = $id
                ? $this->service->findById($id)
                : $this->service->getAll()->paginate(request('per_page', 15));

            $resource = $id
                ? ProtocolResource::make($protocols)
                : ProtocolResource::collection($protocols);

            return $this->sendSuccess(
                $resource,
                $id ? 'Protocol retrieved successfully.' : 'Protocols retrieved successfully.',
                $id ? null : pagination($protocols)
            );

        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


    public function createFirst(ProtocolFirstStepRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $protocol = $this->service->create($request->except('images'));
            $this->service->saveImages($protocol, $request['images']);
            DB::commit();
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createSecond(?int $id, ProtocolSecondStepRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $protocol = $this->service->update($id, $request->except('files'));
            $this->service->saveFiles($protocol, $request['files']);
            DB::commit();
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function createThird(?int $id, ProtocolThirdStepRequest $request): JsonResponse
    {
        try {
            $protocol = $this->service->update($id, $request->validated());
            return $this->sendSuccess(ProtocolResource::make($protocol), 'Protocol created successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
