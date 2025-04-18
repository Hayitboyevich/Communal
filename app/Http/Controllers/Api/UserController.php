<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

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

    public function edit($id, UserUpdateRequest $request): JsonResponse
    {
        try {
            $user = $this->service->update($id, $request);
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function info(): JsonResponse
    {
        try {
            $data = $this->service->getInfo(request('pin'), request('birth_date'));

            return $this->sendSuccess($data, 'Passport Information Get Successfully');

        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function organization(): JsonResponse
    {
        try {
            $cadNumber = request('stir');
            $response = Http::withBasicAuth('orgapi-v1', '*@org-apiv_*ali')
                ->get('https://api-sert.mc.uz/api/orginfoapi/' . $cadNumber);

            if ($response->successful()) {
                return response()->json($response->json());
            }
            return $this->sendError(ErrorMessage::ERROR_1);
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function inspector($id = null): JsonResponse
    {
        try {
            $filters = request()->only(['region_id', 'full_name', 'phone', 'pin']);

            $users = $this->service->getInspectors($filters)->get();

           return $this->sendSuccess(UserResource::collection($users), 'Inspectors retrieved successfully.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }
}
