<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Superadmin\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantByPath
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantSlug = $request->route('tenant');

        if (!$tenantSlug || $request->is('tenant-debug*')) {
            return $next($request);
        }

        $possibleIds = array_values(array_unique([
            $tenantSlug,
            'tenant_' . $tenantSlug,
            'tenant' . $tenantSlug,
            Str::slug($tenantSlug),
        ]));

        $possibleDatabaseNames = array_values(array_unique([
            $tenantSlug,
            'tenant_' . $tenantSlug,
            'tenant' . $tenantSlug,
            Str::slug($tenantSlug),
        ]));

        $lookupConnectionName = config('database.default', env('DB_CONNECTION', 'mysql'));
        $lookupConnectionConfig = config("database.connections.$lookupConnectionName", []);

        Log::info('Tenant bootstrap lookup started', [
            'tenant_slug' => $tenantSlug,
            'request_url' => $request->getSchemeAndHttpHost() . $request->getRequestUri(),
            'host' => $request->getHost(),
            'path' => $request->path(),
            'lookup_connection' => $lookupConnectionName,
            'lookup_table' => 'tenants',
            'lookup_connection_details' => [
                'driver' => $lookupConnectionConfig['driver'] ?? null,
                'host' => $lookupConnectionConfig['host'] ?? null,
                'port' => $lookupConnectionConfig['port'] ?? null,
                'database' => $lookupConnectionConfig['database'] ?? null,
            ],
        ]);

        try {
            $tenantQuery = Tenant::on($lookupConnectionName)->query()->where(function ($query) use ($tenantSlug) {
                $query->where('id', 'tenant_' . $tenantSlug)
                    ->orWhere('database_name', $tenantSlug);
            });

            Log::info('Tenant bootstrap lookup SQL', [
                'sql' => $tenantQuery->toSql(),
                'bindings' => $tenantQuery->getBindings(),
                'connection' => $lookupConnectionName,
            ]);

            $tenant = $tenantQuery->first();

            if (!$tenant) {
                Log::info('Tenant bootstrap lookup returned no result', [
                    'requested_slug' => $tenantSlug,
                    'checked_ids' => $possibleIds,
                    'checked_database_names' => $possibleDatabaseNames,
                ]);
            } else {
                Log::info('Tenant bootstrap lookup succeeded', [
                    'tenant_id' => $tenant->id,
                    'business_id' => $tenant->business_id ?? null,
                    'db_host' => $tenant->data['db_host'] ?? null,
                    'db_port' => $tenant->data['db_port'] ?? null,
                    'db_name' => $tenant->data['db_name'] ?? null,
                    'db_username' => $tenant->data['db_username'] ?? null,
                ]);
            }
        } catch (\Throwable $lookupException) {
            $diagnostic = $this->buildLookupFailureResponse(
                $tenantSlug,
                $possibleIds,
                $possibleDatabaseNames,
                $lookupConnectionName,
                $lookupConnectionConfig,
                $lookupException,
            );

            Log::error('Tenant lookup failed', array_merge($diagnostic, [
                'exception_class' => get_class($lookupException),
                'trace' => $lookupException->getTraceAsString(),
                'file' => $lookupException->getFile(),
                'line' => $lookupException->getLine(),
            ]));

            return response()->json($diagnostic, 503);
        }

        if (!$tenant) {
            $centralConnection = config("database.connections.$lookupConnectionName", []);

            return response()->json([
                'message' => 'Tenant not found',
                'requested_slug' => $tenantSlug,
                'root_cause' => 'tenant record not found',
                'lookup_connection' => $lookupConnectionName,
                'lookup_table' => 'tenants',
                'checked_lookup' => [
                    'ids' => $possibleIds,
                    'database_names' => $possibleDatabaseNames,
                ],
                'central_database' => $centralConnection['database'] ?? null,
                'central_connection' => [
                    'driver' => $centralConnection['driver'] ?? null,
                    'host' => $centralConnection['host'] ?? null,
                    'port' => $centralConnection['port'] ?? null,
                    'database' => $centralConnection['database'] ?? null,
                ],
            ], 404);
        }

        $connectionProbe = $this->probeTenantDatabaseConnection($tenant);
        if (!$connectionProbe['success']) {
            Log::error('Tenant database connection probe failed', array_merge($connectionProbe, [
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenantSlug,
            ]));

            return response()->json($connectionProbe, 503);
        }

        session([
            'tenant_id' => $tenant->id,
            'current_tenant_id' => $tenant->id,
            'sticky_tenant_id' => $tenant->id,
        ]);

        $request->merge(['tenant' => $tenant->id]);

        return $next($request);
    }

    private function probeTenantDatabaseConnection($tenant): array
    {
        $tenantConnectionName = 'tenant';
        $credentials = is_array($tenant->data) ? $tenant->data : [];

        if (empty($credentials['db_host']) || empty($credentials['db_name']) || empty($credentials['db_username'])) {
            return [
                'message' => 'Tenant database credentials are incomplete',
                'root_cause' => 'missing_tenant_db_credentials',
                'exception_message' => 'Tenant data for db_host, db_name or db_username is missing',
                'connection_name' => $tenantConnectionName,
            ];
        }

        $password = '';
        if (!empty($credentials['db_password'])) {
            try {
                $password = decrypt($credentials['db_password']);
            } catch (\Throwable $e) {
                $password = $credentials['db_password'];
            }
        }

        Config::set("database.connections.$tenantConnectionName", [
            'driver' => 'mysql',
            'host' => $credentials['db_host'],
            'port' => $credentials['db_port'] ?? 3306,
            'database' => $credentials['db_name'],
            'username' => $credentials['db_username'],
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]);

        try {
            DB::purge($tenantConnectionName);
            DB::reconnect($tenantConnectionName);
            $selectedDatabase = DB::connection($tenantConnectionName)->getDatabaseName();
            $tables = DB::connection($tenantConnectionName)->select('SHOW TABLES LIMIT 5');
            $userCount = DB::connection($tenantConnectionName)->selectOne('SELECT COUNT(*) as count FROM users');

            Log::info('Tenant database connection probe succeeded', [
                'connection_name' => $tenantConnectionName,
                'selected_database' => $selectedDatabase,
                'tables' => $tables,
                'user_count' => $userCount->count ?? null,
            ]);

            return [
                'success' => true,
                'connection_name' => $tenantConnectionName,
                'selected_database' => $selectedDatabase,
                'tables' => $tables,
                'user_count' => $userCount->count ?? null,
            ];
        } catch (\Throwable $exception) {
            $details = $this->buildExceptionPayload($exception, $tenantConnectionName);
            $details['success'] = false;
            $details['message'] = 'Tenant database connection probe failed';
            $details['root_cause'] = $this->classifyFailure($exception->getMessage())['category'];
            $details['reason'] = $this->classifyFailure($exception->getMessage())['reason'];
            $details['selected_database'] = $credentials['db_name'] ?? null;

            return $details;
        }
    }

    private function buildLookupFailureResponse(
        string $tenantSlug,
        array $possibleIds,
        array $possibleDatabaseNames,
        string $lookupConnectionName,
        array $lookupConnectionConfig,
        \Throwable $exception,
    ): array {
        $payload = $this->buildExceptionPayload($exception, $lookupConnectionName);

        $payload['message'] = 'Tenant lookup failed';
        $payload['cause'] = $this->classifyFailure($exception->getMessage());
        $payload['lookup_table'] = 'tenants';
        $payload['requested_slug'] = $tenantSlug;
        $payload['checked_lookup'] = [
            'ids' => $possibleIds,
            'database_names' => $possibleDatabaseNames,
        ];
        $payload['central_database'] = $lookupConnectionConfig['database'] ?? null;
        $payload['central_connection'] = [
            'driver' => $lookupConnectionConfig['driver'] ?? null,
            'host' => $lookupConnectionConfig['host'] ?? null,
            'port' => $lookupConnectionConfig['port'] ?? null,
            'database' => $lookupConnectionConfig['database'] ?? null,
        ];

        return $payload;
    }

    private function buildExceptionPayload(\Throwable $exception, string $connectionName): array
    {
        $payload = [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'sqlstate' => null,
            'pdo_error_code' => null,
            'connection_name' => $connectionName,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'previous_exception' => null,
        ];

        if ($exception instanceof QueryException) {
            $payload['sql'] = $exception->getSql();
            $payload['bindings'] = $exception->getBindings();
        }

        if (preg_match('/SQLSTATE\[(?<sqlstate>[A-Z0-9]+)\]\s*\[(?<code>\d+)\]/', $exception->getMessage(), $matches)) {
            $payload['sqlstate'] = $matches['sqlstate'];
            $payload['pdo_error_code'] = $matches['code'];
        }

        if ($exception->getPrevious() instanceof \Throwable) {
            $previous = $exception->getPrevious();
            $payload['previous_exception'] = [
                'exception_class' => get_class($previous),
                'exception_message' => $previous->getMessage(),
                'file' => $previous->getFile(),
                'line' => $previous->getLine(),
            ];
        }

        return $payload;
    }

    private function classifyFailure(string $message): array
    {
        $normalizedMessage = strtolower($message);

        if (str_contains($normalizedMessage, 'no connection could be made')
            || str_contains($normalizedMessage, 'connection refused')
            || str_contains($normalizedMessage, 'timed out')
            || str_contains($normalizedMessage, 'connection timed out')) {
            return [
                'category' => 'network_or_db_host',
                'reason' => 'incorrect DB host or network issue',
            ];
        }

        if (str_contains($normalizedMessage, 'could not find driver')) {
            return [
                'category' => 'missing_pdo_driver',
                'reason' => 'the PHP PDO driver for the configured database is missing',
            ];
        }

        if (str_contains($normalizedMessage, 'unknown database')
            || str_contains($normalizedMessage, 'database') && str_contains($normalizedMessage, 'does not exist')) {
            return [
                'category' => 'incorrect_database_name',
                'reason' => 'database name does not exist on the host',
            ];
        }

        if (str_contains($normalizedMessage, 'access denied')
            || str_contains($normalizedMessage, 'authentication failed')
            || str_contains($normalizedMessage, 'password')) {
            return [
                'category' => 'incorrect_credentials',
                'reason' => 'database credentials are invalid',
            ];
        }

        if (str_contains($normalizedMessage, 'base table or view not found')
            || str_contains($normalizedMessage, 'no such table')
            || str_contains($normalizedMessage, "table '") && str_contains($normalizedMessage, " doesn't exist")) {
            return [
                'category' => 'missing_tenants_table',
                'reason' => 'the tenants table is missing on the lookup database',
            ];
        }

        return [
            'category' => 'unknown_database_error',
            'reason' => 'unclassified database failure',
        ];
    }
}
