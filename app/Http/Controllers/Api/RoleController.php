<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends BaseController
{
    public function index($id = null): JsonResponse
    {
        try {
            $roles = $id
                ? Role::query()->findOrFail($id)
                : Role::query()->paginate(request('per_page', 15));

            $resource = $id
                ? RoleResource::make($roles)
                : RoleResource::collection($roles);

            return $this->sendSuccess(
                $resource,
                $id ? 'Role retrieved successfully.' : 'Roles retrieved successfully.',
                $id ? null : pagination($roles)
            );
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
