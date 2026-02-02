<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AttendanceSetting extends Model
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
        
        // Prepare the effective settings to be saved (handling booleans/arrays)
        foreach ($allSettings as $key => $setting) {
            // Handle booleans (missing from request = unchecked)
            if ($setting->type === 'boolean' && !isset($settings[$key])) {
                $settings[$key] = 'false';
            }
            
            // Handle json/arrays (missing from request = empty)
            if ($setting->type === 'json' && !isset($settings[$key])) {
                $settings[$key] = [];
            }
        }
        
        foreach ($settings as $key => $value) {
            $setting = $allSettings->get($key);
            
            if (!$setting) {
                continue; // Skip unknown keys
            }

            // Prepare value for validation and storage
            $processedValue = $value;
            if ($setting->type === 'json' && is_array($value)) {
                $processedValue = json_encode($value);
            } elseif ($setting->type === 'boolean') {
                $processedValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            // Perform validation
            $rules = $setting->validation_rules ?? [];
            if ($setting->type === 'boolean') {
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

            // Only save if the value has actually changed to minimize DB and Cache ops
            $stringValue = is_bool($processedValue) ? ($processedValue ? 'true' : 'false') : (string)$processedValue;
            if ($setting->value !== $stringValue) {
                $setting->update(['value' => $stringValue]);
                
                // Clear specific cache
                Cache::forget(self::CACHE_PREFIX . $key);
            }
        }

        // Always clear the "all" cache if we successfully updated anything
        if (empty($errors)) {
            Cache::forget(self::CACHE_ALL_KEY);
        }

        return $errors;
    }
}
