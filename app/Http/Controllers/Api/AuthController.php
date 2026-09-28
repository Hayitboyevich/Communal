<?php

namespace App\Http\Controllers\Api;

use App\Constants\ErrorMessage;
use App\Enums\UserStatusEnum;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ShaffofIdTokenRequest;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use App\Services\EimzoService;
use App\Services\EmploymentIntegrationService;
use App\Services\OneTimeTokenService;
use App\Services\ShaffofIdIntegrationService;
use App\Services\UserService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    public function __construct(private EimzoService                          $eimzoService,
                                private readonly EmploymentIntegrationService $employmentIntegrationService,
                                private readonly ShaffofIdIntegrationService  $shaffofIdIntegrationService,
                                private readonly OneTimeTokenService          $oneTimeTokenService
    )
    {
        parent::__construct();
    }

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
                'full_name' => $user->full_name,
            ];
            return $this->sendSuccess($meta, 'User find.');
        } catch (\Exception $exception) {
            return $this->sendError(ErrorMessage::ERROR_1, $exception->getMessage());
        }
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('login', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->user_status_id != UserStatusEnum::ACTIVE->value)
                return $this->sendError(error: 'Access denied!User is not active.', code: 422);
            $token = JWTAuth::claims(['role_id' => $request->role_id])->fromUser($user);
            $role = Role::query()->find($request->role_id);
            $success['token'] = $token;
            $success['id'] = $user->id;
            $success['name'] = $user->name;
            $success['middle_name'] = $user->middle_name;
            $success['surname'] = $user->surname;
            $success['pin'] = $user->pin;
            $success['role'] = new RoleResource($role);
            $success['region'] = $user->region_id ? new RegionResource($user->region) : null;
            $success['district'] = $user->district_id ? new DistrictResource($user->district) : null;
            $success['image'] = $user->image ? Storage::disk('public')->url($user->image) : null;

            return $this->sendSuccess($success, 'User logged in successfully.');
        } else {
            return response()->json(['error' => 'Unauthorized', "message" => 'Invalid credentials'], 401);
        }
    }

    public function auth(): JsonResponse
    {
        $fromCache = $this->oneTimeTokenService->consume(purpose: 'auth', token: request('token'));
        if (!$fromCache){
            $encodedData = request('token');
            $decodedData = base64_decode($encodedData);
            list($pin, $accessToken) = explode(':', $decodedData);
        } else $pin = $fromCache['pinfl'];

        $user = User::query()->where('pin', $pin)->first();
        if ($user->user_status_id != UserStatusEnum::ACTIVE->value)
            return $this->sendError(error: 'Access denied!User is not active.', code: 422);
        if ($user) {
            Auth::login($user);
            $user = Auth::user();
            $roleId = request('role_id');
            $role = Role::query()->find($roleId);
            $token = JWTAuth::claims(['role_id' => \request('role_id')])->fromUser($user);
            $success['token'] = $token;
            $success['id'] = $user->id;
            $success['name'] = $user->name;
            $success['middle_name'] = $user->middle_name;
            $success['surname'] = $user->surname;
            $success['pin'] = $user->pin;
            $success['role'] = new RoleResource($role);
            $success['region'] = $user->region_id ? new RegionResource($user->region) : null;
            $success['district'] = $user->district_id ? new DistrictResource($user->district) : null;
            $success['image'] = $user->image ? Storage::disk('public')->url($user->image) : null;
            return $this->sendSuccess($success, 'User logged in successfully.');
        } else {
            return $this->sendError('Kirish huquqi mavjud emas', code: 401);
        }
    }

    public function checkEimzoDetached()
    {
        $pkcs7 = request('pkcs7');
        $signTimestamp = $this->eimzoService->signTimestamp($pkcs7);
        return $this->sendSuccess($this->eimzoService->attached($signTimestamp['pkcs7b64']), 'Eimzo detached successfully.');
    }

    public function infoEmployment($pinfl)
    {
        return $this->sendSuccess($this->employmentIntegrationService->currentWorkPlaceOne($pinfl), 'Employment Information Get Successfully');
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    public function getToken(ShaffofIdTokenRequest $request)
    {
        $validated = $request->validated();
        $result = $this->shaffofIdIntegrationService->getAccessToken($validated['code'], $validated['redirect_uri'], $validated['code_verifier']);
        $token = $this->oneTimeTokenService->issue('auth', $result);
        return $this->sendSuccess([
            'roles' => $result['roles'],
            'access_token' => $token,
            'full_name' => $result['name'],
            'id_token' => $result['id_token'],
        ],
            'Token Get Successfully');
    }

    public function logout(): JsonResponse
    {
        $user = $this->user;
        $this->shaffofIdIntegrationService->refreshSession($user->id);
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->sendSuccess(null, 'Logged out successfully.');
    }

    public function refreshSession()
    {
        $idToken = request('id_token');
        $req = Http::get("https://id.shaffofqurilish.uz/oauth/logout?client_id=01a0cdac-95ae-736e-861b-85403355dfaf&id_token_hint=$idToken&post_logout_redirect_uri=https://ujk-nazorat.mc.uz/login");
        $req = $req->getBody()->getContents();
        return $this->sendSuccess($req, 'Session refreshed successfully.');
    }
}
