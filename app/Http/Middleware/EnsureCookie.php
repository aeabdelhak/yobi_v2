<?php

namespace App\Http\Middleware;

use App\Enums\constants;
use Closure;
use Illuminate\Http\Request;

class EnsureCookie
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
        $cookie = $request->cookie(constants::$refreshToken);
        if ($cookie) {
            $request->headers->set('authorization', "Bearer " . $cookie);
        }

        return $next($request);
    }
}