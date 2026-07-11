<?php

namespace Modules\Superadmin\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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
     * Automatically encrypts sensitive settings and clears cache.
     */
    public function set(string $key, mixed $value): SystemSetting
    {
        // Capture old value for audit
        $oldValue = $this->get($key);

        $setting = $this->repo->set($key, $value);

        // Audit log
        $this->audit($key, $oldValue, $value);

        // Bust cache
        $this->clearCache();

        return $setting;
    }

    /**
     * Save multiple settings at once (e.g. from a form).
     * Only saves keys that exist in the database (security filter).
     */
    public function setMany(array $data): void
    {
        $oldAll = $this->loadCache();
        $changed = [];

        foreach ($data as $key => $value) {
            $setting = $this->repo->find($key);

            if (!$setting) {
                continue; // Do not create arbitrary settings from POSTs
            }

            if (!$setting->is_editable) {
                continue; // Non-editable settings are protected
            }

            $oldValue = $oldAll[$key] ?? null;
            $this->repo->set($key, $value);
            $this->audit($key, $oldValue, $value);
            $changed[] = $key;
        }

        if (!empty($changed)) {
            $this->clearCache();
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
     * Force-clear the settings cache. Called after any write.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);

        // Also clear per-category caches
        foreach (SettingCategory::ALL as $category) {
            Cache::forget(self::CACHE_KEY . '_' . $category);
        }
    }

    /**
     * Warm the cache (call during deployment/artisan optimize).
     */
    public function warmCache(): void
    {
        $this->clearCache();
        $this->loadCache();
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
