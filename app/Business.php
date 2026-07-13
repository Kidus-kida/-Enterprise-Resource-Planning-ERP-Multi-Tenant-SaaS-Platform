<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Business extends Model
{

    protected static $logAttributes = ['*'];

    protected static $logFillable = true;

    protected static $logName = 'Business';
    /**
     * The database connection that should be used by the model.
     * Business data is stored in the Master database.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'businesses';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['fillable', 'some_other_attribute']);
    }

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'woocommerce_api_settings'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['woocommerce_api_settings'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'pos_settings' => 'array',
        'keyboard_shortcuts' => 'array',
        'ref_no_prefixes' => 'array',
        'ref_no_starting_number' => 'array',
        'enabled_modules' => 'array',
        'email_settings' => 'array',
        'sms_settings' => 'array',
        'common_settings' => 'array',
        'contact_fields' => 'array',
        'owner_invite_sent_at' => 'datetime',
        'owner_activated_at' => 'datetime',
    ];

    /**
     * Returns the date formats
     */
    public static function date_formats()
    {
        return [
            // 'd-m-Y' => 'dd-mm-yyyy',
            // 'm-d-Y' => 'mm-dd-yyyy',
            'd/m/Y' => 'dd/mm/yyyy',
            'm/d/Y' => 'mm/dd/yyyy'
        ];
    }

    /**
     * Get the owner details
     */
    public function owner()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'owner_id');
    }

    /**
     * Get the Business currency.
     */
    public function currency()
    {
        return $this->belongsTo(\App\Currency::class);
    }

    /**
     * Get the Business currency.
     */
    public function locations()
    {
        return $this->hasMany(\App\BusinessLocation::class);
    }

    /**
     * Get the Business printers.
     */
    // public function printers()
    // {
    //     return $this->hasMany(\App\Printer::class);
    // }

    /**
     * Get the Business subscriptions history.
     */
    public function subscriptions()
    {
        return $this->hasMany('\Modules\Superadmin\Models\Subscription');
    }

    /**
     * Get the current Business subscription.
     */
    public function subscription()
    {
        return $this->hasOne('\Modules\Superadmin\Models\Subscription')->latestOfMany();
    }

    /**
     * Get the tenant record for this business.
     */
    public function tenant()
    {
        return $this->hasOne('\Modules\Superadmin\Models\Tenant', 'business_id');
    }

    /**
     * Resolve the enabled modules for this business from the active subscription
     * when the business-level module list has not been populated yet.
     */
    public function resolveEnabledModules(): array
    {
        $enabledModules = $this->enabled_modules ?? [];

        if (is_string($enabledModules)) {
            $enabledModules = json_decode($enabledModules, true) ?: [];
        }

        if (!is_array($enabledModules)) {
            $enabledModules = [];
        }

        if (!empty($enabledModules)) {
            return array_values(array_unique(array_filter($enabledModules)));
        }

        $subscription = $this->subscriptions()
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->latest()
            ->first();

        if ($subscription) {
            $permissions = is_array($subscription->module_activation_details) ? $subscription->module_activation_details : [];

            if (empty($permissions) && $subscription->package) {
                $permissions = is_array($subscription->package->custom_permissions) ? $subscription->package->custom_permissions : [];
            }

            $enabledModules = array_keys(array_filter($permissions));
        }

        $enabledModules = array_values(array_unique(array_filter($enabledModules)));

        if (!empty($enabledModules)) {
            $this->forceFill(['enabled_modules' => $enabledModules])->saveQuietly();
        }

        return $enabledModules;
    }

    /**
     * Get the companies for the business.
     */
    public function companies()
    {
        return $this->hasMany(\App\Company::class);
    }

    /**
     * Get the package associated with this business.
     */
    public function package()
    {
        return $this->belongsTo('\Modules\Superadmin\Models\Package', 'package_id');
    }

    /**
     * Get manual payments for this business.
     */
    public function manualPayments()
    {
        return $this->hasMany('\Modules\Superadmin\Models\ManualPayment');
    }

    /**
     * Creates a new business based on the input provided.
     *
     * @return object
     */
    public static function create_business($details)
    {
        $business = Business::create($details);
        return $business;
    }

    /**
     * Updates a business based on the input provided.
     * @param int $business_id
     * @param array $details
     *
     * @return object
     */
    public static function update_business($business_id, $details)
    {
        if (!empty($details)) {
            Business::where('id', $business_id)
                ->update($details);
        }
    }
}
