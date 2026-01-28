<?php

namespace Modules\Superadmin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserType;

class SuperadminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access this area.');
        }

        // SECURITY: Only allow access for users with UserType::SUPERADMIN
        // Do NOT use role-based checks here as tenants can create roles named 'superadmin'
        if (auth()->user()->type === UserType::SUPERADMIN) {  
            return $next($request);
        }

        // If condition is not met, deny access
        abort(403, 'Unauthorized. System Owner access required.');
    }
}
