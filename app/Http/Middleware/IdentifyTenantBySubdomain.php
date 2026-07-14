<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Superadmin\Models\Domain;
use Modules\Superadmin\Models\Tenant;
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
            $host = $request->getHost();
            $centralDomain = env('CENTRAL_DOMAIN');
            $appHost = parse_url(config('app.url', env('APP_URL', 'http://localhost')), PHP_URL_HOST);
            $candidateDomains = array_values(array_filter([
                $centralDomain,
                $appHost,
                str_replace('www.', '', $centralDomain),
                str_replace('www.', '', $appHost),
            ]));

            \Log::info('IdentifyTenantBySubdomain: Host = ' . $host . ', Central = ' . ($centralDomain ?? 'null') . ', AppHost = ' . ($appHost ?? 'null'));

            if (in_array($host, $candidateDomains, true) || in_array('www.' . $host, $candidateDomains, true)) {
                \Log::info('IdentifyTenantBySubdomain: Accessing the base domain, skipping tenant resolution');
                session()->forget('current_tenant_id');
                return $next($request);
            }

            $subdomain = $this->extractSubdomain($host, $candidateDomains);

            if ($subdomain && $subdomain !== 'www') {
                \Log::info('IdentifyTenantBySubdomain: Extracted subdomain = ' . $subdomain);

                $tenant = Tenant::resolveByIdentifier($subdomain);

                if ($tenant) {
                    session(['current_tenant_id' => $tenant->id]);
                    $request->merge(['tenant' => $tenant->id]);
                    \Log::info('IdentifyTenantBySubdomain: Found tenant ID = ' . $tenant->id);
                } else {
                    session()->forget('current_tenant_id');
                    \Log::warning('IdentifyTenantBySubdomain: No tenant found for host ' . $host . ' (Sub: ' . $subdomain . ') - Session cleared');
                }
            } else {
                \Log::info('IdentifyTenantBySubdomain: No subdomain detected (' . $host . ') - Preserving existing session if any.');
            }
        } catch (\Exception $e) {
            \Log::error("IdentifyTenantBySubdomain Error: " . $e->getMessage());
        }
        
        return $next($request);
    }
    
    /**
     * Extract subdomain from host
     */
    private function extractSubdomain(string $host, array $candidateDomains): ?string
    {
        foreach ($candidateDomains as $domain) {
            if (empty($domain)) {
                continue;
            }

            if ($host === $domain || $host === 'www.' . $domain) {
                return null;
            }

            if (Str::endsWith($host, '.' . $domain)) {
                return Str::beforeLast($host, '.' . $domain);
            }
        }

        $labels = explode('.', $host);
        if (count($labels) > 2) {
            return $labels[0];
        }

        return null;
    }
}
