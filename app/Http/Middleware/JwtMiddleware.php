<?php

namespace App\Http\Middleware;

use App\Builder\ReturnApi;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return ReturnApi::error("Não autorizado", null, 401);
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return ReturnApi::error("Token Inválido", null, 401);
            }
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return ReturnApi::error("Token Expirado", null, 401);
            }

            return ReturnApi::error("Não Autorizado", null, 401);
        }

        return $next($request);
    }
}
