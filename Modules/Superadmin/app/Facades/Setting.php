<?php

namespace Modules\Superadmin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Setting Facade
 *
 * @method static mixed  get(string $key, mixed $default = null)
 * @method static \Modules\Superadmin\Models\SystemSetting set(string $key, mixed $value)
 * @method static void   setMany(array $data)
 * @method static array  group(string $category)
 * @method static array  all()
 * @method static \Illuminate\Support\Collection getCategorySettings(string $category)
 * @method static \Illuminate\Support\Collection search(string $query)
 * @method static int    restoreDefaults(string $category)
 * @method static void   clearCache()
 * @method static void   warmCache()
 * @method static array  export()
 * @method static array  import(array $data)
 *
 * @see \Modules\Superadmin\Services\SettingsService
 */
class Setting extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'settings';
    }
}
