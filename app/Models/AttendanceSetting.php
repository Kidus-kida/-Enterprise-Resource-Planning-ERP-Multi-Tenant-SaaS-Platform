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
        
        // Fetch ALL current settings in one go to avoid N+1 queries
        $allSettings = self::all()->keyBy('key');
        
        // Pre-process settings to handle array-like keys (e.g. working_days[])
        $processedRequest = [];
        foreach ($settings as $key => $value) {
            $cleanKey = str_replace('[]', '', $key);
            $processedRequest[$cleanKey] = $value;
        }

        // Determine which categories are present in the request
        $presentCategories = [];
        foreach ($processedRequest as $key => $value) {
            $setting = $allSettings->get($key);
            if ($setting) {
                $presentCategories[$setting->category] = true;
            }
        }

        // Handle checkboxes - boolean settings in present categories that are missing from request
        foreach ($allSettings as $key => $setting) {
            if ($setting->type === 'boolean' && !isset($processedRequest[$key])) {
                if (isset($presentCategories[$setting->category])) {
                    $processedRequest[$key] = 'false';
                }
            }
        }
        
        // Prepare the effective settings to be saved (handling booleans/arrays)
        foreach ($processedRequest as $key => $value) {
            $setting = $allSettings->get($key);
            if (!$setting) {
                continue; // Skip settings that don't exist in our database
            }
            
            $value = $processedRequest[$key];
            $processedValue = $value;

            // Decode JSON strings for json type settings (e.g., when frontend sends '[]' for empty arrays)
            if ($setting->type === 'json' && is_string($value) && !empty($value) && in_array($value[0], ['[', '{'])) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $processedValue = $decoded;
                }
            }

            // Convert processed values to string format for storage comparison
            if ($setting->type === 'json') {
                if (is_string($processedValue) && empty($processedValue)) {
                    $processedValue = [];
                }
                $stringValue = json_encode(is_array($processedValue) ? $processedValue : []);
            } elseif ($setting->type === 'boolean') {
                $processedValue = filter_var($processedValue, FILTER_VALIDATE_BOOLEAN);
                $stringValue = $processedValue ? 'true' : 'false';
            } else {
                $stringValue = (string)$processedValue;
            }

            // Perform validation
            $rules = $setting->validation_rules ?? [];
            if ($setting->type === 'boolean' || $setting->type === 'json') {
                $rules = array_filter($rules, fn($r) => $r !== 'required');
            }

            $validator = \Validator::make(
                [$key => $processedValue],
                [$key => $rules]
            );

            if ($validator->fails()) {
                $errors[$key] = $validator->errors()->get($key);
                continue;
            }

            // Only save if the value has actually changed
            if ($setting->value !== $stringValue) {
                $setting->update(['value' => $stringValue]);
                Cache::forget(self::CACHE_PREFIX . $key);
            }
        }

        Cache::forget(self::CACHE_ALL_KEY);
        return $errors;
    }
}
