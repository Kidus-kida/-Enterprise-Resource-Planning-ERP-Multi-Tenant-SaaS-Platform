<?php

if (!function_exists('setting')) {
    /**
     * Get a setting value using dot notation.
     *
     * This is a shorthand for Setting::get() — ideal for Blade templates.
     *
     * Usage:
     *   setting('appearance.primary_color')
     *   setting('general.system_name', 'My ERP')
     *
     * @param string $key     Dot-notation key (e.g. "appearance.primary_color")
     * @param mixed  $default Default value if key not found
     * @return mixed
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return app('settings')->get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (!function_exists('setting_group')) {
    /**
     * Get all settings for a category as key → value array.
     *
     * Usage:
     *   setting_group('appearance')
     *
     * @param string $category Category name (use SettingCategory constants)
     * @return array
     */
    function setting_group(string $category): array
    {
        try {
            return app('settings')->group($category);
        } catch (\Throwable) {
            return [];
        }
    }
}
