<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GetMenuMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Simply pass the request to the next middleware
        return $next($request);
    }
} 