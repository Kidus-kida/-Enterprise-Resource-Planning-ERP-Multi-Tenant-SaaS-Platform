<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as key-value array
     */
    public static function all_settings()
    {
        return self::pluck('value', 'key')->toArray();
    }

    /**
     * Get default settings
     */
    public static function defaults()
    {
        return [
            'pension_employee_percent' => '7',
            'pension_employer_percent' => '11',
            'overtime_regular_rate' => '1.5',
            'overtime_sunday_rate' => '2.0',
            'overtime_holiday_rate' => '2.5',
            'taxable_allowance_regular' => '600',
            'taxable_allowance_managerial' => '2200',
            'pay_period' => 'monthly',
            'working_days_per_week' => '5',
            'working_hours_per_day' => '8',
        ];
    }
}
