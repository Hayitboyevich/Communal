<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthMiddleware
{
    public function __construct(protected Guard $auth){}

    public function handle($request, Closure $next)
    {
        return $this->auth->basic('login') ?: $next($request);
    }
}
