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

        // Allow access for user with ID = 1 (Superadmin)
        if (auth()->user()->type === UserType::SUPERADMIN) {  
            return $next($request);
        }

        // Check if user has superadmin role (if using spatie/laravel-permission)
        $user = auth()->user();
        if (method_exists($user, 'hasRole') && $user->hasRole('superadmin')) {
            return $next($request);
        }

        // If neither condition is met, deny access
        abort(403, 'Unauthorized. Superadmin access required.');
    }
}
