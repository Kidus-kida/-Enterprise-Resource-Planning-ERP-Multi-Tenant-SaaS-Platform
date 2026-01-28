<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HandleDatabaseErrors
{
    /**
     * Handle an incoming request and catch database connection errors
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip database checks for certain routes
        if ($this->shouldSkipDatabaseCheck($request)) {
            return $next($request);
        }

        try {
            return $next($request);
            
        } catch (\Exception $e) {
            // Catch any database-related exceptions
            if ($this->isDatabaseError($e)) {
                return $this->handleDatabaseError($request, $e->getMessage());
            }
            
            // Re-throw non-database exceptions
            throw $e;
        }
    }

    /**
     * Check if we should skip database checks for this request
     */
    private function shouldSkipDatabaseCheck(Request $request): bool
    {
        $skipRoutes = [
            'diagnostic*',
            'test-route',
            'test-dashboard',
            'test-auth',
            'login',
            'register',
            'password*',
            'forgot-password',
            'reset-password*',
            '/',
            'apps',
            'pricing',
            'industries',
            'services',
            'resources',
            'test-alpine'
        ];

        foreach ($skipRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if database is connected
     */
    private function isDatabaseConnected(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            \Log::error("Database connection test failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if exception is database-related
     */
    private function isDatabaseError(\Exception $e): bool
    {
        $message = $e->getMessage();
        
        return strpos($message, 'Access denied') !== false ||
               strpos($message, 'Connection refused') !== false ||
               strpos($message, 'Unknown database') !== false ||
               strpos($message, 'SQLSTATE') !== false ||
               $e instanceof \PDOException ||
               $e instanceof \Illuminate\Database\QueryException;
    }

    /**
     * Handle database errors gracefully
     */
    private function handleDatabaseError(Request $request, string $message): Response
    {
        // Skip database error handling for diagnostic routes
        if ($request->is('diagnostic*')) {
            return response()->view('errors.database-connection', ['message' => $message], 500);
        }

        // For API requests, return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Database connection failed',
                'message' => 'Unable to connect to the database. Please check your configuration.',
                'diagnostic_url' => url('/diagnostic'),
            ], 500);
        }

        // For web requests, show user-friendly error page
        return response()->view('errors.database-connection', [
            'message' => 'Unable to connect to the database. Please check your configuration.',
        ], 500);
    }
}