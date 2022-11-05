<?php

namespace App\Http\Middleware;

use App\Enums\constants;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;

class EnsureStoreAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $store = $request->cookie(constants::$storeCookieName);

        if (Auth::user()->hasAccess($store)) {
            return $next($request);
        }
        return response('you have no acceess to this store', 401);

    }
}