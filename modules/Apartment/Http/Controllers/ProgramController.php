<?php

namespace Modules\Apartment\Http\Controllers;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Modules\Apartment\Http\Requests\ProgramRequest;
use Modules\Apartment\Http\Resources\ProgramResource;
use Modules\Apartment\Services\ProgramService;

class ProgramController extends BaseController
{
    public function __construct(protected ProgramService $service)
    {
        parent::__construct();
    }

    public function index($id = null): JsonResponse
    {
        try {
            $data = $id
                ? $this->service->findById($id)
                : $this->service->getAll()->orderBy('created_at', 'desc')->paginate(request('per_page', 15));

            $resource = $id
                ? ProgramResource::make($data)
                : ProgramResource::collection($data);

            return $this->sendSuccess(
                $resource,
                $id ? 'Protocol retrieved successfully.' : 'Protocols retrieved successfully.',
                $id ? null : pagination($data)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(ProgramRequest  $request): JsonResponse
    {
        try {
            $program = $this->service->create($request);
            return $this->sendSuccess(ProgramResource::make($program), 'Created program');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }


}
