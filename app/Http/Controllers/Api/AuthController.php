<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function auth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'login' => 'required',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::query()->create($validator->validated());
        $token = $user->createToken('UserToken')->accessToken;
        return response()->json([$user, 'token' => $token], 201);
    }

    public function login(Request $request)
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


            return response()->json($meta, 201);
        }else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
