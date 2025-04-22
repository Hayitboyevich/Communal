<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthenticatedException;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

class CheckRoleMiddleware
{

    protected $user;
    protected $roleId;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        if (Auth::guard('api')->user()) {
            $this->user = Auth::guard('api')->user();
            if ($this->user)  $this->roleId = $this->user->getRoleFromToken();
            if (in_array($this->roleId, $this->user->roles->pluck('id')->toArray())) {
                return $next($request);
            }else{
                throw new UnauthenticatedException('Unauthenticated.', 401);
            }
        }else{
            throw new UnauthenticatedException('Unauthenticated.', 401);
        }
    }
}
