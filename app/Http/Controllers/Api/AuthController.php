<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Http\Controllers\BaseController;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AuthController extends BaseController
{

    public function checkUser(): JsonResponse
    {
        try {
            $url = 'https://sso.egov.uz/sso/oauth/Authorization.do?grant_type=one_authorization_code
            &client_id=' . config('services.oneId.id') .
                '&client_secret=' . config('services.oneId.secret') .
                '&code=' . request('code') .
                '&redirect_url=' . config('services.oneId.redirect');
            $resClient = Http::post($url);
            $response = json_decode($resClient->getBody(), true);


            $url = 'https://sso.egov.uz/sso/oauth/Authorization.do?grant_type=one_access_token_identify
            &client_id=' . config('services.oneId.id') .
                '&client_secret=' . config('services.oneId.secret') .
                '&access_token=' . $response['access_token'] .
                '&Scope=' . $response['scope'];
            $resClient = Http::post($url);
            $data = json_decode($resClient->getBody(), true);


            $user = User::query()
                ->where('pin', $data['pin'])
                ->first();

            if (!$user) throw new ModelNotFoundException('Foydalanuvchi topilmadi');

            $combinedData = $data['pin'] . ':' . $response['access_token'];

            $encodedData = base64_encode($combinedData);

            $meta = [
                'roles' => RoleResource::collection($user->roles),
                'access_token' => $encodedData,
                'full_name' => $user->full_name
            ];
            return $this->sendSuccess($meta, 'User find.');
        }catch (\Exception $exception){
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function login(): JsonResponse
    {
        $encodedData = request('token');
        $decodedData = base64_decode($encodedData);
        list($pin, $accessToken) = explode(':', $decodedData);

        $user = User::query()->where('pin', $pin)->first();
        if ($user){
            Auth::login($user);
            $user = Auth::user();
            $roleId = request('role_id');
            $role = Role::query()->find($roleId);
            $token = $user->createToken('App', ['role_id' => $roleId])->accessToken;

            $success['token'] = $token;
            $success['id'] = $user->id;
            $success['full_name'] = $user->name;
            $success['pin'] = $user->pin;
            $success['role'] = new RoleResource($role);
            $success['region'] = $user->region_id ? new RegionResource($user->region) : null;
            $success['district'] = $user->district_id ?  new DistrictResource($user->district) : null;
            $success['image'] = $user->image ?  Storage::disk('public')->url($user->image): null;
            return $this->sendSuccess($success, 'User logged in successfully.');
        }else{
            return $this->sendError('Kirish huquqi mavjud emas', code: 401);
        }
    }
}
