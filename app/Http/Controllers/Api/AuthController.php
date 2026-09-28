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
        $req = Http::get('https://id.shaffofqurilish.uz/oauth/logout?client_id=01a0cdac-95ae-736e-861b-85403355dfaf&id_token_hint=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImM5ZmNiMGE0ZjU3MTQ4YTUifQ.eyJpc3MiOiJodHRwczovL2lkLnNoYWZmb2ZxdXJpbGlzaC51eiIsImF1ZCI6IjAxYTBjZGFjLTk1YWUtNzM2ZS04NjFiLTg1NDAzMzU1ZGZhZiIsInN1YiI6IjU2YjZjODNkLTA5NzUtNDE4NS1hNjgwLWZlMmNmMDFjODEyZiIsImlhdCI6MTc5MDU5ODk3Ny42MTMzMjksIm5iZiI6MTc5MDU5ODk3Ny42MTMzMjksImV4cCI6MTc5MDYwMjU3Ny42MTMzMjksImp0aSI6ImUzY2JiMjY1NzNhNTNhZDI1ZmNhZWQzYjJmM2I3YTE3IiwiYXV0aF90aW1lIjoxNzkwNTk4ODczLCJub25jZSI6IllxZ3lqUFo5SVgyZ3RaZ2tFaEhqMTdMTVV0RDNweGtDIiwiYXRfaGFzaCI6Ii11Vkp6RlBQWmd4UHpYdFdfdkJnNEEiLCJuYW1lIjoiWE9MSVFVTE9WIEpBTVNISUQgQkFYVElZT1IgT-KAmEfigJhMSSIsImZhbWlseV9uYW1lIjoiWE9MSVFVTE9WIiwiZ2l2ZW5fbmFtZSI6IkpBTVNISUQiLCJtaWRkbGVfbmFtZSI6IkJBWFRJWU9SIE_igJhH4oCYTEkiLCJiaXJ0aGRhdGUiOiIxOTk3LTA5LTIzIiwibG9jYWxlIjoidXoiLCJ1cGRhdGVkX2F0IjoxNzkwNTk4ODczLCJwaW5mbCI6IjMyMzA5OTc2NjAwMDE2IiwicGFzc3BvcnRfbnVtYmVyIjoiQUQ1MDAwNjM4IiwiZWltem8iOnsic3ViamVjdF90eXBlIjoiaW5kaXZpZHVhbCIsInNlcmlhbF9udW1iZXIiOiI3OGE2YWEzOSIsImNvbW1vbl9uYW1lIjoiWE9MSVFVTE9WIEpBTVNISUQgQkFYVElZT1IgT-KAmEfigJhMSSIsInZhbGlkX2Zyb20iOiIyMDI1LTAxLTE3IDEwOjQ1OjQyIiwidmFsaWRfdG8iOiIyMDI3LTAxLTE3IDEwOjQ1OjQyIn0sImFtciI6WyJvbmVpZCJdLCJzdWJqZWN0X3R5cGUiOiJpbmRpdmlkdWFsIn0.KE8Dax3ASiUrjlfVE_2XTWu6C2Xk8UyQgtPpTu-xswg2YnkhBY28A2SABGSAl-l4N2pDloUc6RAEbKNgaEsZxBnt0-uuMhmIDyjwCfBiDNpbQBqvg1XpkOICPBcke2PSI6df2iYNWNOl4SFR5N2WbVK5Aa7c4I-1ebkAj31JHh5BB5FEIj5ZcnD_CCcXc5owYylBhD-Ob3Ekivi0SMyZ2LGDhrs-6eZudOa5rwCn2OC7Tg0l4RCF1hIFWaAxVYe9IUZe0Y3ym7C1EslXKsohA6BoiQopj-NaHLcMaVPcBXlD2SnER1ycRWw6LV6Z66KgrwiaFquYQGn_9BAZUio9R4WBfjffBDFO9b2gPGZgTg5gC0ktffXRLttYOGzwSsO4k7zADbbyzHkqSNZ_7JzjUV66jRQEWnIE4zyX3rmds1p96uYAYYDtC-rRhpe7XRyAgEMJusixMLp70fQs4Y4bpMfWoHC_r_3Yi8PPDrd1N1FvkD7rneJaMRXV5M-reI36tG43xVyDhSjVkupRIiD56CZgzOv5ptz_819bdbgkkWOqNyhcRQLVSmQMCfTwClSm-hIC2v2A1tT-S8hlB7gAmEqmGR5mHSMMDY4MxG6_w77xJRFF4YarNT-TePefXS7u5m1m6GcTjVMhOwg7eTSxZ8A6xmrm1cEViFgQI03yqeU&post_logout_redirect_uri=https://ujk-nazorat.mc.uz/login');
        $req = $req->getBody()->getContents();
        return $this->sendSuccess($req, 'Session refreshed successfully.');
    }
}
