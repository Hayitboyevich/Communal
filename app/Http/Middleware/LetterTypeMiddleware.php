<?php

namespace App\Http\Middleware;

use App\Enums\LetterType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LetterTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $request->attributes->set('letter_type', LetterType::from((int) $type));
        return $next($request);
    }
}
