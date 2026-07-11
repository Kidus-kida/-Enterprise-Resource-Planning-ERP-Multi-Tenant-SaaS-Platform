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
        $centralDomain = env('CENTRAL_DOMAIN');
        
        // Fallback to APP_URL host if not specified
        if (empty($centralDomain)) {
            $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);
        }
        
        $host = $request->getHost();
        
        // Allow if host matches central domain
        if ($host === $centralDomain) {
            return $next($request);
        }
        
        // Allow localhost/IP for local dev
        if ($host === 'localhost' || $host === '127.0.0.1') {
             return $next($request);
        }

        // Otherwise abort - Superadmin panel is NOT reachable from subdomains
        abort(404);
    }
}
