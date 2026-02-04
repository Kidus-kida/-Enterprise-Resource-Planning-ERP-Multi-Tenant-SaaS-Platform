<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CentralDomainOnly
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
        $centralDomain = env('CENTRAL_DOMAIN', 'ettech.et');
        $host = $request->getHost();
        
        // Allow if host matches central domain
        if ($host === $centralDomain) {
            return $next($request);
        }
        
        // Allow localhost/IP for local dev if central domain is not set or matches
        if ($host === 'localhost' || $host === '127.0.0.1') {
             return $next($request);
        }

        // Otherwise abort - Superadmin panel is NOT reachable from subdomains
        abort(404);
    }
}
