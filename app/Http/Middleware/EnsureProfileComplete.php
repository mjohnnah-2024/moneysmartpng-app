<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()
            && ! $request->user()->profile
            && ! $request->routeIs('onboarding.*')
            && ! $request->routeIs('logout')
        ) {
            return redirect()->route('onboarding.show');
        }

        return $next($request);
    }
}
