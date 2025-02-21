<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserCreateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends BaseController
{

    public function __construct( protected UserService  $service)
    {

    }
    public function index($id = null)
    {
        try {

        }catch (\Exception $exception){
            return $this->sendError('Xatolik aniqlandi.', $exception->getMessage());
        }
    }

    public function create(UserCreateRequest $request): JsonResponse
    {
        try {
            $user = $this->service->create($request->validated());

            return $this->sendSuccess(new UserResource($user), 'User created successfully.');

        }catch (\Exception $exception){
            return $this->sendError('Xatolik aniqlandi.', $exception->getMessage());
        }
    }
}
