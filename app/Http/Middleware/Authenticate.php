<?php

namespace App\Http\Middleware;

use App\Enums\userStatus;
use Closure;
use Exception;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware;

class Authenticate extends BaseMiddleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */

    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user && in_array($user->status, [userStatus::$active, userStatus::$superAdmin])) {
                return $next($request);
            } else {
                return response()->json(['status' => 'invalid token'], 403);
            }

        } catch (Exception $e) {
            return response()->json(['status' => 'invalid'], 403);
        }

    }
}