<?php

namespace Modules\Superadmin\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Modules\Superadmin\Models\SystemSetting;

/**
 * SettingsRepository — raw database access for settings.
 * Always go through SettingsService (which adds caching) rather than this directly.
 */
class SettingsRepository
{
    /**
     * Get a single setting row by dot-notation key.
     */
    public function find(string $key): ?SystemSetting
    {
        return SystemSetting::where('key', $key)->first();
    }

    /**
     * Get the raw string value stored in the database.
     */
    public function getRaw(string $key): ?string
    {
        return SystemSetting::where('key', $key)->value('value');
    }

    /**
     * Set (create or update) a setting value.
     * Encrypts automatically for sensitive settings.
     *
     * @return SystemSetting
     */
    public function set(string $key, mixed $value, int $updatedBy = null): SystemSetting
    {
        $setting = SystemSetting::where('key', $key)->first();

        if (!$setting) {
            // Auto-create a simple text setting if it doesn't exist
            $setting = new SystemSetting([
                'key'      => $key,
                'category' => explode('.', $key)[0] ?? 'general',
                'type'     => 'string',
                'input_type' => 'text',
            ]);
        }

        // Encode arrays/json
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            if ($setting->type === 'string') {
                $setting->type = 'json';
            }
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } else {
            $value = (string) $value;
        }

        // Encrypt sensitive values
        if ($setting->is_sensitive && !empty($value)) {
            $value = Crypt::encryptString($value);
        }

        $setting->value      = $value;
        $setting->updated_by = $updatedBy ?? auth()->id();
        $setting->save();

        return $setting;
    }

    /**
     * Get all settings for a category, ordered.
     */
    public function getByCategory(string $category): Collection
    {
        return SystemSetting::byCategory($category)->ordered()->get();
    }

    /**
     * Get all settings grouped by category.
     */
    public function getAll(): Collection
    {
        return SystemSetting::ordered()->get();
    }

    /**
     * Get all settings grouped by key → typed_value for fast lookup.
     */
    public function getAllAsKeyValue(): array
    {
        $settings = [];
        SystemSetting::ordered()->get()->each(function (SystemSetting $s) use (&$settings) {
            $settings[$s->key] = $s->typed_value;
        });
        return $settings;
    }

    /**
     * Restore all settings in a category to their default_value.
     */
    public function restoreDefaults(string $category): int
    {
        $settings = SystemSetting::byCategory($category)->editable()->get();
        $count = 0;

        foreach ($settings as $setting) {
            if ($setting->default_value !== null) {
                $setting->value      = $setting->default_value;
                $setting->updated_by = auth()->id();
                $setting->save();
                $count++;
            }
        }

        return $count;
    }

    /**
     * Search settings by key, label, description, or section.
     */
    public function search(string $query): Collection
    {
        return SystemSetting::where(function ($q) use ($query) {
            $q->where('key', 'like', "%{$query}%")
              ->orWhere('label', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%")
              ->orWhere('section', 'like', "%{$query}%")
              ->orWhere('category', 'like', "%{$query}%");
        })
        ->where('is_system', false)
        ->ordered()
        ->limit(30)
        ->get();
    }

    /**
     * Get settings with their dependencies resolved.
     * Returns only visible settings based on dependency conditions.
     */
    public function getWithDependencies(string $category): Collection
    {
        $settings = $this->getByCategory($category);
        $allSettings = $this->getAllAsKeyValue();

        return $settings->filter(function ($setting) use ($allSettings) {
            return $this->isDependencySatisfied($setting, $allSettings);
        });
    }

    /**
     * Check if a setting's dependency condition is satisfied.
     */
    private function isDependencySatisfied(SystemSetting $setting, array $allSettings): bool
    {
        if (!$setting->depends_on) {
            return true;
        }

        // Parse dependency: "key:expected_value"
        if (!str_contains($setting->depends_on, ':')) {
            return true;
        }

        [$dependentKey, $expectedValue] = explode(':', $setting->depends_on, 2);
        $actualValue = $allSettings[$dependentKey] ?? null;

        return (string) $actualValue === (string) $expectedValue;
    }

    /**
     * Bulk update multiple settings in a transaction.
     * All-or-nothing: if any setting fails, all are rolled back.
     */
    public function bulkUpdate(array $settings): bool
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }

            \Illuminate\Support\Facades\DB::commit();
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Bulk settings update failed', [
                'error' => $e->getMessage(),
                'settings' => array_keys($settings),
            ]);
            throw $e;
        }
    }

    /**
     * Get settings by section within category.
     */
    public function getBySection(string $category, string $section): Collection
    {
        return SystemSetting::byCategory($category)
            ->bySection($section)
            ->ordered()
            ->get();
    }

    /**
     * Check if setting is editable by current user.
     * This is a placeholder for more complex permission logic.
     */
    public function isEditable(string $key): bool
    {
        $setting = $this->find($key);
        
        if (!$setting) {
            return false;
        }

        // System settings are never editable
        if ($setting->is_system) {
            return false;
        }

        // Check if marked as editable
        if (!$setting->is_editable) {
            return false;
        }

        // Additional permission checks could go here
        // e.g., check if user has 'manage_system_settings' permission
        
        return true;
    }

    /**
     * Get default value for a setting.
     */
    public function getDefault(string $key): mixed
    {
        $setting = $this->find($key);
        return $setting?->default_value;
    }
}
