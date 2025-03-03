<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('login', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('UserToken')->accessToken;
            $meta['name'] = $user->name;
            $meta['surname'] = $user->surname;
            $meta['middle_name'] = $user->middle_name;
            $meta['phone'] = $user->phone;
            $meta['token'] = $token;

            return $this->sendSuccess($meta, 'User logged in successfully.');
        }else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
