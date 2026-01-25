<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Superadmin\Models\Domain;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantBySubdomain
{
    /**
     * Handle an incoming request.
     * Extracts tenant ID from subdomain and stores in session.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for Superadmin and Diagnostic routes
        if ($request->is('superadmin*') || $request->is('diagnostic*')) {
            return $next($request);
        }

        try {
            // Get the full host (e.g., tenant.tewostechsolutions.com)
            $host = $request->getHost();
            
            // Check if we have a subdomain
            $centralDomain = config('tenancy.central_domain', 'tewostechsolutions.com');
            
            \Log::info("IdentifyTenantBySubdomain: Host = $host, Central = $centralDomain");
            
            // If accessing the central domain directly, skip tenant identification
            if ($host === $centralDomain || $host === 'www.' . $centralDomain) {
                \Log::info("IdentifyTenantBySubdomain: Accessing central domain, skipping tenant resolution");
                return $next($request);
            }
            
            // Extract subdomain
            $subdomain = $this->extractSubdomain($host, $centralDomain);
            
            if ($subdomain && $subdomain !== 'www') {
                \Log::info("IdentifyTenantBySubdomain: Extracted subdomain = $subdomain");
                
                // Look up tenant by domain
                $domain = Domain::where('domain', $host)->first();
                
                if ($domain && $domain->tenant_id) {
                    // Store tenant ID in session
                    session(['current_tenant_id' => $domain->tenant_id]);
                    
                    // Also add to request for immediate use
                    $request->merge(['tenant' => $domain->tenant_id]);
                    
                    \Log::info("IdentifyTenantBySubdomain: Found tenant ID = {$domain->tenant_id}");
                } else {
                    \Log::warning("IdentifyTenantBySubdomain: No tenant found for domain $host");
                }
            }
        } catch (\Exception $e) {
            \Log::error("IdentifyTenantBySubdomain Error: " . $e->getMessage());
        }
        
        return $next($request);
    }
    
    /**
     * Extract subdomain from host
     */
    private function extractSubdomain(string $host, string $centralDomain): ?string
    {
        // Remove central domain from host to get subdomain
        $subdomain = str_replace('.' . $centralDomain, '', $host);
        
        // If nothing changed, no subdomain exists
        if ($subdomain === $host) {
            return null;
        }
        
        return $subdomain;
    }
}
