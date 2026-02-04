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
            // Get the full host (e.g., tenant.ettech.et)
            $host = $request->getHost();
            
            // Check if we have a subdomain
            $centralDomain = env('CENTRAL_DOMAIN', 'ettech.et');
            
            \Log::info("IdentifyTenantBySubdomain: Host = $host, Central = $centralDomain");
            
            // If accessing the central domain directly, skip tenant identification
            if ($host === $centralDomain || $host === 'www.' . $centralDomain) {
                \Log::info("IdentifyTenantBySubdomain: Accessing central domain, skipping tenant resolution");
                session()->forget('current_tenant_id'); // Ensure we are on central DB
                return $next($request);
            }
            
            // Extract subdomain
            $subdomain = $this->extractSubdomain($host, $centralDomain);
            
            if ($subdomain && $subdomain !== 'www') {
                \Log::info("IdentifyTenantBySubdomain: Extracted subdomain = $subdomain");
                
                // Look up tenant by domain (custom domain)
                $domain = Domain::where('domain', $host)->first();
                
                if ($domain && $domain->tenant_id) {
                    // Found by Custom Domain
                    session(['current_tenant_id' => $domain->tenant_id]);
                    $request->merge(['tenant' => $domain->tenant_id]);
                    \Log::info("IdentifyTenantBySubdomain: Found tenant ID (Custom Domain) = {$domain->tenant_id}");
                } else {
                    // Fallback: Look up by Subdomain in Businesses table
                    $business = \App\Business::where('subdomain', $subdomain)->first();
                    
                    if ($business && $business->tenant_id) {
                        // Found by Subdomain
                        session(['current_tenant_id' => $business->tenant_id]);
                        $request->merge(['tenant' => $business->tenant_id]);
                        \Log::info("IdentifyTenantBySubdomain: Found tenant ID (Subdomain) = {$business->tenant_id}");
                    } else {
                        // Not found in either
                        // IMPORTANT: If we are on a subdomain/domain but it doesn't match a tenant,
                        // we MUST clear any previous tenant session.
                        session()->forget('current_tenant_id');
                        \Log::warning("IdentifyTenantBySubdomain: No tenant found for domain $host (Sub: $subdomain) - Session cleared");
                    }
                }
            } else {
                // No subdomain (e.g. localhost, IP address)
                // In local dev, we might be using stick/query param sessions.
                // Do NOT clear session here, or we break the persistent login loop.
                // If user wants to switch to Main, they can Logout.
                \Log::info("IdentifyTenantBySubdomain: No subdomain detected ($host) - Preserving existing session if any.");
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
