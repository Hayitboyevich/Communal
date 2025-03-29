<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserCreateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends BaseController
{

    public function __construct(protected UserService  $service){
        parent::__construct();
    }
    public function index($id = null): JsonResponse
    {
        try {
            $users = $id
                ? $this->service->findById($id)
                : $this->service->getAll()->paginate(request('per_page', 15));

            $resource = $id
                ? UserResource::make($users)
                : UserResource::collection($users);

            return $this->sendSuccess(
                $resource,
                $id ? 'User retrieved successfully.' : 'Users retrieved successfully.',
                $id ? null : pagination($users)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function create(UserCreateRequest $request): JsonResponse
    {
        try {
            $user = $this->service->create($request);
            return $this->sendSuccess(new UserResource($user), 'User created successfully.');

        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
