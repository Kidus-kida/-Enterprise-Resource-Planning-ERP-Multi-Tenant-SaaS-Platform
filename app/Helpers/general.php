<?php

use App\Events\AppMenuEvent;
use App\Events\AppSettingsMenuEvent;
use App\Helpers\AppMenu;
use App\Settings\ThemeSettings;
use App\Settings\LocalizationSettings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Facades\Module;
use Spatie\Menu\Laravel\Link;
use Spatie\Menu\Laravel\Menu;

if (!function_exists('route_is')) {
    function route_is($route = null)
    {
        if (request()->is($route) || request()->routeIs($route) || Route::currentRouteName() == $route) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('resolveBrandAssetUrl')) {
    function resolveBrandAssetUrl($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_string($value) && preg_match('#^https?://#i', $value)) {
            return $value;
        }

        if (is_string($value) && str_starts_with($value, '/')) {
            return $value;
        }

        if (is_string($value) && str_contains($value, 'storage/')) {
            return Storage::url($value);
        }

        if (is_string($value) && file_exists(public_path($value))) {
            return asset($value);
        }

        if (is_string($value) && Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }

        return is_string($value) ? asset($value) : null;
    }
}

if(!function_exists('brandingAsset')){
    function brandingAsset(string $key = 'logo', ?string $fallback = null): ?string
    {
        $settingKeys = match ($key) {
            'logo' => ['company_logo', 'company.logo', 'whitelabel.logo', 'company.logo_light'],
            'dark_logo', 'logo_dark' => ['dark_logo', 'company.dark_logo', 'whitelabel.logo_dark'],
            'login_logo' => ['login_logo', 'company.login_logo', 'appearance.login_logo', 'whitelabel.login_logo'],
            'favicon' => ['favicon', 'company.favicon', 'whitelabel.favicon'],
            'login_background' => ['login_background', 'appearance.login_background', 'whitelabel.login_background'],
            'app_background' => ['app_background', 'appearance.app_background', 'whitelabel.app_background'],
            'sidebar_logo' => ['company.sidebar_logo', 'whitelabel.sidebar_logo'],
            default => ['whitelabel.' . $key],
        };

        foreach ($settingKeys as $settingKey) {
            $configured = setting($settingKey);
            if (!empty($configured)) {
                return resolveBrandAssetUrl($configured);
            }
        }

        $publicFallbacks = match ($key) {
            'logo' => ['images/logo.png', 'images/logo.svg', 'images/main-logo.png', 'images/logo.jpg', 'storage/settings/logo.png', 'storage/settings/logo.svg'],
            'dark_logo', 'logo_dark' => ['images/logo-dark.png', 'images/logo-light.png', 'images/logo.png', 'images/logo.svg', 'images/main-logo.png'],
            'login_logo' => ['images/logo.png', 'images/login-logo.png', 'images/brand-logo.png', 'images/main-logo.png', 'storage/settings/login-logo.png'],
            'favicon' => ['favicon.ico', 'images/favicon.ico', 'images/favicon.png', 'images/logo.png'],
            'login_background' => ['images/placeholder.jpg', 'images/laptop.png'],
            'app_background' => ['images/placeholder.jpg', 'images/laptop.png'],
            'sidebar_logo' => ['images/main-logo.png', 'images/logo.png'],
            default => [],
        };

        foreach ($publicFallbacks as $candidate) {
            if (!empty($candidate) && file_exists(public_path($candidate))) {
                return asset($candidate);
            }
        }

        $fallbackPath = $fallback ?? null;

        return $fallbackPath ? asset($fallbackPath) : null;
    }
}

if (!function_exists('brand')) {
    function brand(string $key, $default = null)
    {
        if ($key === 'name') {
            foreach (['company_name', 'company.name', 'whitelabel.app_name', 'whitelabel.name', 'company.name'] as $settingKey) {
                $value = setting($settingKey);
                if (!empty($value)) {
                    return (string) $value;
                }
            }

            $configuredAppName = config('app.name');
            if (!empty($configuredAppName) && !in_array(strtolower($configuredAppName), ['laravel', 'erp', 'erp system', 'md code inc.', 'md code inc'], true)) {
                return (string) $configuredAppName;
            }

            return $default ?? 'ERP System';
        }

        return brandingAsset($key, $default);
    }
}

if(!function_exists('appBrandName')){
    function appBrandName(): string
    {
        return brand('name', 'ERP System');
    }
}

if(!function_exists('appLogo')){
    function appLogo(){
        return brandingAsset('logo');
    }
}

if (!function_exists('route_is')) {
    function route_is($routes = [])
    {
        foreach ($routes as $route) {
            if (request()->is($route) || request()->routeIs($route) || Route::currentRouteName() == $route) {
                return true;
            } else {
                return false;
            }
        }
    }
}

if (!function_exists('json_parse')) {
    function json_parse(array $data)
    {
        return htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
    }
}


/**
 * Generate a random string, using a cryptographically secure
 * pseudorandom number generator (random_int)
 *
 * This function uses type hints now (PHP 7+ only), but it was originally
 * written for PHP 5 as well.
 *
 * For PHP 7, random_int is a PHP core function
 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
 *
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters
 *                         to select from
 * @return string
 */
function random_str(
    int $length = 64,
    string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
    $keyspace = str_shuffle($keyspace);
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    
    // Ensure first character is a letter to be safe for HTML IDs
    $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $pieces[] = $letters[random_int(0, strlen($letters) - 1)];
    
    for ($i = 1; $i < $length; ++$i) {
        $pieces[] = $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

if (!function_exists('format_date')) {
    /**
     * Custom Date Formatter
     *
     * @param string|date $date
     * @param string $format
     * @return void
     */
    function format_date($date, $format = '')
    {
        if($format === ''){
            $format = !empty(LocaleSettings('date_format')) ? LocaleSettings('date_format'): 'Y-m-d';
        }
        return date_format(date_create($date), $format);
    }
}

if(!function_exists('format_file_size')){
    function format_file_size($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}


if (!function_exists('renderAppMenu')) {
    function renderAppMenu()
    {
        if (request()->routeIs('tenant.dashboard')) {
            return '';
        }

        // Try getting custom menu structure
        try {
            $customMenu = setting('menu.structure');
            if ($customMenu) {
                $structure = is_string($customMenu) ? json_decode($customMenu, true) : $customMenu;
                if (is_array($structure) && count($structure) > 0) {
                    $menu = \Spatie\Menu\Laravel\Menu::new()->addClass('sidebar-vertical');
                    $user = auth()->user();
                    if ($user) {
                        foreach ($structure as $item) {
                            // Check visibility
                            if (isset($item['is_visible']) && !$item['is_visible']) {
                                continue;
                            }
                            if (isset($item['is_disabled']) && $item['is_disabled']) {
                                continue;
                            }
                            if (isset($item['permissions']) && !empty($item['permissions'])) {
                                if (!$user->canAny($item['permissions']) && $user->type !== \App\Enums\UserType::SUPERADMIN) {
                                    continue;
                                }
                            }
                            if (isset($item['roles']) && !empty($item['roles'])) {
                                if (!$user->hasAnyRole($item['roles']) && $user->type !== \App\Enums\UserType::SUPERADMIN) {
                                    continue;
                                }
                            }

                            // Render menu item
                            if (!empty($item['is_title'])) {
                                $menu->html('<span>' . __($item['label']) . '</span>', ['class' => 'menu-title']);
                            } elseif (!empty($item['children'])) {
                                // Submenu
                                $submenu = \Spatie\Menu\Laravel\Menu::new()->addParentClass('submenu');
                                $active = false;
                                foreach ($item['children'] as $child) {
                                    if (isset($child['is_visible']) && !$child['is_visible']) {
                                        continue;
                                    }
                                    if (isset($child['is_disabled']) && $child['is_disabled']) {
                                        continue;
                                    }
                                    if (isset($child['permissions']) && !empty($child['permissions'])) {
                                        if (!$user->canAny($child['permissions']) && $user->type !== \App\Enums\UserType::SUPERADMIN) {
                                            continue;
                                        }
                                    }

                                    $route = $child['url'] ?? $child['route'] ?? '#';
                                    $isActiveChild = false;
                                    if (!empty($route) && $route !== '#') {
                                        try {
                                            $routePattern = $child['route_pattern'] ?? $route;
                                            $isActiveChild = route_is($routePattern);
                                        } catch (\Throwable) {
                                            $isActiveChild = false;
                                        }
                                    }
                                    if ($isActiveChild) {
                                        $active = true;
                                    }
                                    
                                    $linkLabel = __($child['label']);
                                    if (!empty($child['badge_count'])) {
                                        $linkLabel .= ' <span class="badge rounded-pill bg-primary float-end">' . $child['badge_count'] . '</span>';
                                    }
                                    
                                    if (!empty($child['is_external'])) {
                                        $link = \Spatie\Menu\Laravel\Link::to($route, $linkLabel)
                                            ->addClass($isActiveChild ? 'active' : '')
                                            ->setAttribute('target', '_blank');
                                    } else {
                                        $link = \Spatie\Menu\Laravel\Link::toRoute($route, $linkLabel)
                                            ->addClass($isActiveChild ? 'active' : '')
                                            ->setAttributes(['wire:navigate' => 'true']);
                                    }

                                    if (isset($child['color'])) {
                                        $link->setAttribute('style', 'color: ' . $child['color']);
                                    }

                                    $submenu->add($link);
                                }
                                // Only add submenu if it has items
                                if ($submenu->setActive($active)->hasItems()) {
                                    $iconClass = $item['icon'] ?? 'la la-folder';
                                    $menuHtml = \Spatie\Menu\Laravel\Html::raw('<a href="#" class="' . ($active ? 'active' : '') . '" ' . (isset($item['color']) ? 'style="color: ' . $item['color'] . ';"' : '') . '><i class="' . $iconClass . '"></i> <span>' . __($item['label']) . '</span><span class="menu-arrow"></span></a>');
                                    $menu->submenu($menuHtml, $submenu);
                                }
                            } else {
                                // Standard root link
                                $route = $item['url'] ?? $item['route'] ?? '#';
                                $isActiveItem = false;
                                if (!empty($route) && $route !== '#') {
                                    try {
                                        $routePattern = $item['route_pattern'] ?? $route;
                                        $isActiveItem = route_is($routePattern);
                                    } catch (\Throwable) {
                                        $isActiveItem = false;
                                    }
                                }
                                
                                $iconHtml = '';
                                if (!empty($item['icon'])) {
                                    $iconHtml = '<i class="' . $item['icon'] . '"></i> ';
                                }
                                $linkLabel = $iconHtml . '<span>' . __($item['label']) . '</span>';
                                if (!empty($item['badge_count'])) {
                                    $linkLabel .= ' <span class="badge rounded-pill bg-primary float-end">' . $item['badge_count'] . '</span>';
                                }

                                if (!empty($item['is_external'])) {
                                    $link = \Spatie\Menu\Laravel\Link::to($route, $linkLabel)
                                        ->setActive($isActiveItem)
                                        ->setAttribute('target', '_blank');
                                } else {
                                    $link = \Spatie\Menu\Laravel\Link::toRoute($route, $linkLabel)
                                        ->setActive($isActiveItem)
                                        ->setAttributes(['wire:navigate' => 'true']);
                                }

                                if (isset($item['color'])) {
                                    $link->setAttribute('style', 'color: ' . $item['color']);
                                }
                                $menu->add($link);
                            }
                        }
                    }
                    return $menu->render();
                }
            }
        } catch (\Throwable $e) {
            // Log or fallback
        }

        $appMenu = new AppMenu();
        
        // Check if we are in Superadmin mode (superadmin routes)
        if (route_is('superadmin.*')) {
            event(new \App\Events\AppSuperadminMenuEvent($appMenu));
        } else {
            // Normal mode
            event(new AppMenuEvent($appMenu));
        }
        
        return $appMenu->menu->render();
    }
}

/**
 * Get App Settings by providing the Settings Class
 */

if (!function_exists('getSetting')) {
    function getSetting($class)
    {
        return app($class);
    }
}

/**
 * Get App Theme Settings
 */
if (!function_exists('Theme')) {
    function Theme($property = null)
    {
        try {
            $settings = app(ThemeSettings::class);

            if (!empty($property)) {
                return $settings->$property ?? null;
            }

            return $settings;
        } catch (\Throwable $e) {
            $fallbacks = [
                'layout' => 'vertical',
                'color_scheme' => 'orange',
                'layout_width' => 'fluid',
                'layout_position' => 'fluid',
                'topbar_color' => 'default',
                'sidebar_view' => 'default',
                'sidebar_color' => 'dark',
                'sidebar_size' => 'lg',
                'font_color' => '#1f1f1f',
            ];

            if (!empty($property)) {
                return $fallbacks[$property] ?? null;
            }

            return new class {
                public function __get($name)
                {
                    return null;
                }
            };
        }
    }
}


/**
 * Get App Locale Settings
 */
if (!function_exists('LocaleSettings')) {
    function LocaleSettings($property = null)
    {
        try {
            $settings = app(LocalizationSettings::class);

            if (!empty($property)) {
                return $settings->$property ?? null;
            }

            return $settings;
        } catch (\Throwable $e) {
            $fallbacks = [
                'lang' => 'en',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
            ];

            if (!empty($property)) {
                return $fallbacks[$property] ?? null;
            }

            return new class {
                public function __get($name)
                {
                    return null;
                }
            };
        }
    }
}

/**
 * Get App Salary Settings
 */
if (!function_exists('SalarySettings')) {
    function SalarySettings($property = null)
    {
        return !empty($property) ? app(\App\Settings\SalarySetting::class)->$property : app(\App\Settings\SalarySetting::class);
    }
}



if (!function_exists('uploadedAsset')) {
    function uploadedAsset($asset, $directory = '')
    {
        return ($directory !== '') ? asset("storage/$directory/$asset") : asset("storage/$asset");
    }
}


if (!function_exists('renderAppSettingsMenu')) {
    function renderAppSettingsMenu()
    {
        $appMenu = new AppMenu();
        event(new AppSettingsMenuEvent($appMenu));
        return $appMenu->settingsMenu->render();
    }
}


function pad_zeros($number, $total_zeros = 4){
    return sprintf("%0{$total_zeros}d", $number);
}


if(!function_exists('module')){
    function module($name)
    {
        $module = Module::find($name);
        if(!empty($module)){
            return $module;
        }
    }
}

if(!function_exists('notify')){
    function notify($message , $type='success'){
        return array(
            'message'=> $message,
            'alert-type' => $type,
        );
    }
}


/**
 * return if auth user has a permission
 *
 * @param string $permission
 * @return bool
 */
if(!function_exists('can')){
    function can($permission){
        return auth('web')->user()->hasPermissionTo($permission);
    }
}

if (!function_exists('format_quantity')) {
    function format_quantity($quantity)
    {
        $precision = 2;
        if (session()->has('business.quantity_precision')) {
            $precision = session('business.quantity_precision');
        }
        
        $decimal_separator = session('currency.decimal_separator', '.');
        $thousand_separator = session('currency.thousand_separator', ',');
        
        return number_format((float)$quantity, $precision, $decimal_separator, $thousand_separator);
    }
}

if (!function_exists('num_format')) {
    function num_format($number)
    {
        $precision = 2;
        if (session()->has('business.currency_precision')) {
            $precision = session('business.currency_precision');
        }
        
        $decimal_separator = session('currency.decimal_separator', '.');
        $thousand_separator = session('currency.thousand_separator', ',');
        
        return number_format((float)$number, $precision, $decimal_separator, $thousand_separator);
    }
}

if (!function_exists('num_format')) {
    function num_format($number, $precision = null)
    {
        $precision = $precision ?? config('constants.currency_precision', 2);
        return number_format((float)$number, $precision, session('currency')['decimal_separator'] ?? '.', session('currency')['thousand_separator'] ?? ',');
    }
}

if (!function_exists('format_quantity')) {
    function format_quantity($number, $precision = null)
    {
        $precision = $precision ?? config('constants.quantity_precision', 2);
        return number_format((float)$number, $precision, session('currency')['decimal_separator'] ?? '.', session('currency')['thousand_separator'] ?? ',');
    }
}

if (!function_exists('format_currency')) {
    function format_currency($number, $symbol = true)
    {
        $precision = config('constants.currency_precision', 2);
        $formatted = number_format((float)$number, $precision, session('currency')['decimal_separator'] ?? '.', session('currency')['thousand_separator'] ?? ',');
        
        if (!$symbol) {
            return $formatted;
        }

        if (session("business.currency_symbol_placement") == "before") {
            return (session("currency")["symbol"] ?? '') . " " . $formatted;
        } else {
            return $formatted . " " . (session("currency")["symbol"] ?? '');
        }
    }
}
