<?php

namespace Modules\Superadmin\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Superadmin\Models\SettingsAuditLog;
use Modules\Superadmin\Models\SystemSetting;
use Modules\Superadmin\Repositories\SettingsRepository;
use Modules\Superadmin\Settings\SettingCategory;

/**
 * SettingsService — the central settings engine.
 *
 * Usage:
 *   Setting::get('appearance.primary_color', '#4e73df')
 *   Setting::set('appearance.primary_color', '#ff0000')
 *   Setting::group('appearance')
 *   Setting::all()
 *   setting('appearance.primary_color')   // global helper
 */
class SettingsService
{
    /** Cache key with version. Bump version suffix when schema changes. */
    const CACHE_VERSION = 'v1';
    const CACHE_KEY     = 'system_settings_v1';
    const CACHE_TTL     = null; // null = forever (we bust manually)

    /**
     * Keys that must always come from .env and cannot be overridden from DB.
     */
    const ENV_OVERRIDES = [
        'APP_KEY', 'APP_ENV', 'APP_DEBUG',
        'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
    ];

    private SettingsRepository $repo;
    private string $requestId;

    public function __construct(SettingsRepository $repo)
    {
        $this->repo      = $repo;
        $this->requestId = (string) Str::uuid();
    }

    // =========================================================================
    // Read
    // =========================================================================

    /**
     * Get a single setting value by dot-notation key.
     * Environment variables always override database values.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // 1. Check .env override (ENV keys are UPPER_SNAKE)
        $envKey = strtoupper(str_replace('.', '_', $key));
        if (in_array($envKey, self::ENV_OVERRIDES) && env($envKey) !== null) {
            return env($envKey);
        }

        // 2. Load from cache
        $all = $this->loadCache();

        return $all[$key] ?? $default;
    }

    /**
     * Get all settings for a specific category as key → typed_value.
     */
    public function group(string $category): array
    {
        $all = $this->loadCache();

        return array_filter($all, fn($k) => str_starts_with($k, $category . '.'), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get ALL settings as key → typed_value array.
     */
    public function all(): array
    {
        return $this->loadCache();
    }

    /**
     * Get all SystemSetting model objects for a category (for form rendering).
     */
    public function getCategorySettings(string $category): Collection
    {
        return $this->repo->getByCategory($category);
    }

    /**
     * Search settings by keyword.
     */
    public function search(string $query): Collection
    {
        return $this->repo->search($query);
    }

    // =========================================================================
    // Write
    // =========================================================================

    /**
     * Set a single setting value.
     * Validates, encrypts sensitive settings, and clears cache.
     * 
     * @throws \InvalidArgumentException if validation fails
     */
    public function set(string $key, mixed $value): SystemSetting
    {
        // Validate before saving
        $errors = $this->validate($key, $value);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(
                "Validation failed for {$key}: " . implode(', ', $errors)
            );
        }

        // Capture old value for audit
        $oldValue = $this->get($key);

        $setting = $this->repo->set($key, $value);

        // Audit log
        $this->audit($key, $oldValue, $value);

        // Bust cache
        $this->clearCache();
        
        // Broadcast change for real-time updates
        $this->broadcastChange($key, $value);

        return $setting;
    }

    /**
     * Save multiple settings at once (e.g. from a form).
     * Only saves keys that exist in the database (security filter).
     * Validates all settings before saving any.
     * All-or-nothing transaction: if any setting fails validation, none are saved.
     * 
     * @throws \Illuminate\Validation\ValidationException if validation fails
     */
    public function setMany(array $data): void
    {
        // First pass: validate all settings
        $validationErrors = [];
        foreach ($data as $key => $value) {
            $setting = $this->repo->find($key);

            if (!$setting || !$setting->is_editable) {
                continue;
            }

            $errors = $this->validate($key, $value);
            if (!empty($errors)) {
                $validationErrors[$key] = $errors;
            }
        }

        // If any validation errors, throw exception before saving anything
        if (!empty($validationErrors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($validationErrors);
        }

        // Second pass: save all settings in a transaction (all-or-nothing)
        DB::beginTransaction();
        
        try {
            $oldAll = $this->loadCache();
            $changed = [];

            foreach ($data as $key => $value) {
                $setting = $this->repo->find($key);

                if (!$setting || !$setting->is_editable) {
                    continue;
                }

                $oldValue = $oldAll[$key] ?? null;
                $this->repo->set($key, $value);
                $this->audit($key, $oldValue, $value);
                $changed[] = $key;
                
                // Broadcast change for real-time updates
                $this->broadcastChange($key, $value);
            }

            DB::commit();

            if (!empty($changed)) {
                $this->clearCache();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            
            \Illuminate\Support\Facades\Log::error('Batch settings save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'keys' => array_keys($data),
            ]);
            
            throw $e;
        }
    }

    /**
     * Restore all settings in a category to their default_value.
     */
    public function restoreDefaults(string $category): int
    {
        $count = $this->repo->restoreDefaults($category);
        $this->audit($category . '.*', 'various', 'defaults_restored');
        $this->clearCache();
        return $count;
    }

    // =========================================================================
    // Cache
    // =========================================================================

    /**
     * Load all settings into cache (or retrieve from cache).
     */
    private function loadCache(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return $this->repo->getAllAsKeyValue();
        });
    }

    /**
     * Load settings for a specific category into cache (or retrieve from cache).
     */
    private function loadCategoryCache(string $category): array
    {
        $cacheKey = self::CACHE_KEY . '_' . $category;
        
        return Cache::rememberForever($cacheKey, function () use ($category) {
            return $this->repo->getByCategory($category)
                ->mapWithKeys(fn($s) => [$s->key => $s->typed_value])
                ->all();
        });
    }

    /**
     * Force-clear the settings cache. Called after any write.
     * Clears both global cache and all category-specific caches.
     */
    public function clearCache(): void
    {
        try {
            // Clear global cache
            Cache::forget(self::CACHE_KEY);

            // Clear per-category caches
            foreach (SettingCategory::ALL as $category) {
                Cache::forget(self::CACHE_KEY . '_' . $category);
            }
        } catch (\Throwable $e) {
            // Log cache errors but don't fail the request
            \Illuminate\Support\Facades\Log::warning('Cache clear failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear cache for a specific category only.
     */
    public function clearCategoryCache(string $category): void
    {
        try {
            Cache::forget(self::CACHE_KEY . '_' . $category);
            
            // Also clear global cache since it contains this category
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Category cache clear failed for {$category}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm the cache (call during deployment/artisan optimize).
     * Pre-loads all settings into cache to avoid cold start performance hit.
     */
    public function warmCache(): void
    {
        $this->clearCache();
        
        // Load global cache
        $this->loadCache();
        
        // Pre-warm category-specific caches
        foreach (SettingCategory::ALL as $category) {
            $this->loadCategoryCache($category);
        }
    }

    // =========================================================================
    // Export / Import
    // =========================================================================

    /**
     * Export all non-sensitive settings as a key → value array (for JSON export).
     */
    public function export(): array
    {
        return SystemSetting::where('is_sensitive', false)
            ->ordered()
            ->get()
            ->mapWithKeys(fn($s) => [$s->key => $s->typed_value])
            ->all();
    }

    /**
     * Import settings from an exported array.
     * Only updates keys that already exist in the database.
     */
    public function import(array $data): array
    {
        $updated = [];
        $skipped = [];

        foreach ($data as $key => $value) {
            $setting = $this->repo->find($key);

            if (!$setting || !$setting->is_editable || $setting->is_sensitive) {
                $skipped[] = $key;
                continue;
            }

            $this->repo->set($key, $value);
            $this->audit($key, null, $value);
            $updated[] = $key;
        }

        if (!empty($updated)) {
            $this->clearCache();
        }

        return ['updated' => $updated, 'skipped' => $skipped];
    }

    // =========================================================================
    // Validation
    // =========================================================================

    /**
     * Validate setting value against rules before save.
     * Returns empty array if valid, or array of error messages if invalid.
     */
    public function validate(string $key, mixed $value): array
    {
        $setting = $this->repo->find($key);
        
        if (!$setting || !$setting->validation_rules) {
            return [];
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            [$key => $value],
            [$key => $setting->validation_rules]
        );

        return $validator->fails() ? $validator->errors()->get($key) : [];
    }

    /**
     * Get settings grouped by category and section for display.
     */
    public function getCategorizedSettings(string $category): Collection
    {
        return $this->repo->getByCategory($category)
            ->groupBy('section')
            ->map(function ($sectionSettings) {
                return $sectionSettings->sortBy('sort_order');
            });
    }

    // =========================================================================
    // CSS Variable Generation
    // =========================================================================

    /**
     * Generate CSS variables from appearance settings.
     * Returns a string ready to be injected into a <style> tag.
     */
    public function generateCssVariables(): string
    {
        $colors = $this->group('appearance.colors');
        
        $css = ":root {\n";
        
        foreach ($colors as $key => $value) {
            // Extract color name from key (e.g., appearance.colors.primary -> primary)
            $colorName = str_replace('appearance.colors.', '', $key);
            
            // Base color
            $css .= "    --{$colorName}-color: {$value};\n";
            
            // Generate shade variations
            if ($this->isHexColor($value)) {
                $shades = $this->generateColorShades($value);
                foreach ($shades as $shade => $shadeValue) {
                    $css .= "    --{$colorName}-{$shade}: {$shadeValue};\n";
                }
            }
        }
        
        $css .= "}\n";
        
        return $css;
    }

    /**
     * Check if a value is a valid hex color.
     */
    private function isHexColor(string $value): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $value);
    }

    /**
     * Generate color shade variations (lighter, light, dark, darker).
     */
    private function generateColorShades(string $hex): array
    {
        // Convert hex to RGB
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return [
            'lighter' => $this->rgbToHex(
                min(255, $r + 60),
                min(255, $g + 60),
                min(255, $b + 60)
            ),
            'light' => $this->rgbToHex(
                min(255, $r + 30),
                min(255, $g + 30),
                min(255, $b + 30)
            ),
            'dark' => $this->rgbToHex(
                max(0, $r - 30),
                max(0, $g - 30),
                max(0, $b - 30)
            ),
            'darker' => $this->rgbToHex(
                max(0, $r - 60),
                max(0, $g - 60),
                max(0, $b - 60)
            ),
        ];
    }

    /**
     * Convert RGB values to hex color string.
     */
    private function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    // =========================================================================
    // Integration Testing
    // =========================================================================

    /**
     * Test email configuration by sending a test email.
     */
    public function testEmailConfig(array $config, string $testRecipient): bool
    {
        try {
            // Temporarily configure mail settings
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $config['host'] ?? '',
                    'port' => $config['port'] ?? 587,
                    'encryption' => $config['encryption'] ?? 'tls',
                    'username' => $config['username'] ?? '',
                    'password' => $config['password'] ?? '',
                ],
                'mail.from' => [
                    'address' => $config['from_address'] ?? 'test@example.com',
                    'name' => $config['from_name'] ?? 'Test',
                ],
            ]);

            // Send test email
            \Illuminate\Support\Facades\Mail::raw(
                'This is a test email from your system settings configuration.',
                function ($message) use ($testRecipient) {
                    $message->to($testRecipient)
                            ->subject('Test Email Configuration');
                }
            );

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Email test failed', [
                'error' => $e->getMessage(),
                'config' => array_diff_key($config, ['password' => '']),
            ]);
            throw $e;
        }
    }

    /**
     * Test storage connection.
     */
    public function testStorageConnection(string $driver, array $config): bool
    {
        try {
            // Temporarily configure storage
            $diskConfig = match($driver) {
                's3' => [
                    'driver' => 's3',
                    'key' => $config['key'] ?? '',
                    'secret' => $config['secret'] ?? '',
                    'region' => $config['region'] ?? '',
                    'bucket' => $config['bucket'] ?? '',
                    'endpoint' => $config['endpoint'] ?? null,
                ],
                'ftp' => [
                    'driver' => 'ftp',
                    'host' => $config['host'] ?? '',
                    'username' => $config['username'] ?? '',
                    'password' => $config['password'] ?? '',
                    'port' => $config['port'] ?? 21,
                    'root' => $config['root'] ?? '',
                ],
                default => throw new \Exception("Unsupported storage driver: {$driver}")
            };

            config(["filesystems.disks.test_{$driver}" => $diskConfig]);
            
            // Test by trying to write and read a test file
            $disk = \Illuminate\Support\Facades\Storage::disk("test_{$driver}");
            $testFile = 'test_connection_' . time() . '.txt';
            $disk->put($testFile, 'test');
            $disk->delete($testFile);

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Storage test failed for {$driver}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Test third-party integration connection.
     */
    public function testIntegration(string $integration, array $credentials): bool
    {
        return match($integration) {
            'google_oauth' => $this->testGoogleOAuth($credentials),
            'stripe' => $this->testStripe($credentials),
            'paypal' => $this->testPayPal($credentials),
            default => throw new \Exception("Unsupported integration: {$integration}")
        };
    }

    private function testGoogleOAuth(array $credentials): bool
    {
        // Placeholder for Google OAuth test
        // In production, this would verify client ID and secret with Google API
        if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
            throw new \Exception('Google OAuth credentials are incomplete');
        }
        return true;
    }

    private function testStripe(array $credentials): bool
    {
        // Placeholder for Stripe test
        // In production, this would make a test API call to Stripe
        if (empty($credentials['api_key'])) {
            throw new \Exception('Stripe API key is missing');
        }
        return true;
    }

    private function testPayPal(array $credentials): bool
    {
        // Placeholder for PayPal test
        // In production, this would verify credentials with PayPal API
        if (empty($credentials['client_id']) || empty($credentials['secret'])) {
            throw new \Exception('PayPal credentials are incomplete');
        }
        return true;
    }

    /**
     * Get settings diff between current and defaults for a category.
     */
    public function getDiff(string $category): array
    {
        $settings = $this->repo->getByCategory($category);
        $diff = [];

        foreach ($settings as $setting) {
            if ($setting->value !== $setting->default_value) {
                $diff[$setting->key] = [
                    'current' => $setting->typed_value,
                    'default' => $setting->default_value,
                ];
            }
        }

        return $diff;
    }

    // =========================================================================
    // Broadcasting (for real-time updates)
    // =========================================================================

    /**
     * Broadcast setting change via WebSocket.
     */
    private function broadcastChange(string $key, mixed $value): void
    {
        try {
            // Only broadcast if broadcasting is enabled
            if (config('broadcasting.default') !== 'null') {
                event(new \Modules\Superadmin\Events\SettingChanged($key, $value));
            }
        } catch (\Throwable $e) {
            // Never let broadcast failure break a settings save
            \Illuminate\Support\Facades\Log::debug('Setting broadcast failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    // =========================================================================
    // Internal
    // =========================================================================

    private function audit(string $key, mixed $old, mixed $new): void
    {
        try {
            SettingsAuditLog::record($key, $old, $new, $this->requestId);
        } catch (\Throwable) {
            // Never let audit failure break a settings save
        }
    }
}
