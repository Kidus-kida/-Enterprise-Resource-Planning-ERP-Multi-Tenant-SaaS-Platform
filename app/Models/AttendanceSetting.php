<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AttendanceSetting extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'key', 'category', 'value', 'type', 'label', 
        'description', 'validation_rules', 'display_order'
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'display_order' => 'integer',
    ];

    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'attendance_setting:';
    const CACHE_ALL_KEY = 'attendance_settings:all';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by key with type casting
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value by key
     * 
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public static function set(string $key, $value): self
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string)$value]
        );

        // Clear cache
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::CACHE_ALL_KEY);

        return $setting;
    }

    /**
     * Get all settings grouped by category
     * 
     * @return array
     */
    public static function getAllByCategory(): array
    {
        return Cache::remember(self::CACHE_ALL_KEY, self::CACHE_TTL, function () {
            $settings = self::orderBy('category')->orderBy('display_order')->get();
            
            $grouped = [];
            foreach ($settings as $setting) {
                $grouped[$setting->category][] = [
                    'key' => $setting->key,
                    'value' => self::castValue($setting->value, $setting->type),
                    'raw_value' => $setting->value,
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'validation_rules' => $setting->validation_rules,
                ];
            }
            
            return $grouped;
        });
    }

    /**
     * Get settings for a specific category
     * 
     * @param string $category
     * @return array
     */
    public static function getByCategory(string $category): array
    {
        $all = self::getAllByCategory();
        return $all[$category] ?? [];
    }

    /**
     * Cast value to appropriate type
     * 
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue(string $value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            
            case 'integer':
                return (int) $value;
            
            case 'float':
                return (float) $value;
            
            case 'json':
                return json_decode($value, true) ?? [];
            
            case 'time':
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Clear all settings cache
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        $keys = self::pluck('key');
        
        foreach ($keys as $key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        }
        
        Cache::forget(self::CACHE_ALL_KEY);
    }

    /**
     * Get validation rules for a setting
     * 
     * @param string $key
     * @return array
     */
    public static function getValidationRules(string $key): array
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->validation_rules ?? [] : [];
    }

    /**
     * Validate and set multiple settings
     * 
     * @param array $settings ['key' => 'value', ...]
     * @return array Validation errors if any
     */
    public static function setMultiple(array $settings): array
    {
        $errors = [];
        
        // Fetch all current settings to handle missing checkboxes (booleans)
        $allPossibleSettings = self::all();
        
        foreach ($allPossibleSettings as $possibleSetting) {
            $key = $possibleSetting->key;
            
            // If a boolean setting is missing from the request, it was unchecked
            if (!isset($settings[$key]) && $possibleSetting->type === 'boolean') {
                $settings[$key] = 'false';
            }
            
            // Special case for allowed_methods (json array) if it's missing entirely
            if (!isset($settings['allowed_methods']) && $key === 'allowed_methods') {
                $settings['allowed_methods'] = [];
            }

            // Special case for working_days (json array) if it's missing entirely
            if (!isset($settings['working_days']) && $key === 'working_days') {
                $settings['working_days'] = [];
            }
        }
        
        foreach ($settings as $key => $value) {
            // Find the setting first
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                // Skip unknown keys silently (like _token, require_location, etc)
                // instead of erroring out and failing the whole update.
                continue;
            }

            // Decode JSON strings for json type settings (e.g., when frontend sends '[]' for empty arrays)
            if ($setting->type === 'json' && is_string($value) && in_array($value[0] ?? '', ['[', '{'])) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }

            // Convert array to JSON string for json type settings
            if ($setting->type === 'json' && is_array($value)) {
                $value = json_encode($value);
            }

            if ($setting->type === 'boolean') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            $rules = $setting->validation_rules ?? [];
            if ($setting->type === 'boolean') {
                $rules = array_filter($rules, fn($r) => $r !== 'required');
            }

            $validator = \Validator::make(
                [$key => $value],
                [$key => $rules]
            );

            if ($validator->fails()) {
                $errors[$key] = $validator->errors()->get($key);
                \Log::warning("Validation failed for $key:", ['value' => $value, 'errors' => $errors[$key]]);
                continue;
            }

            \Log::info("Saving setting: $key = " . (is_bool($value) ? ($value ? 'true' : 'false') : $value));
            self::set($key, $value);
        }

        if (!empty($errors)) {
            \Log::error("setMultiple errors:", $errors);
        }

        return $errors;
    }
}

