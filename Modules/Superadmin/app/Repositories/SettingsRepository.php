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
}
