<?php

namespace App\Models;
use DB;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserType;
use App\Models\AttendanceTimestamp;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use \Spatie\Permission\Traits\HasRoles;
    use \App\Traits\HasCompany;

    /**
     * Get the database connection for the model.
     * Dynamically uses 'tenant' connection when configured, otherwise uses default.
     *
     * @return string
     */
    public function getConnectionName()
    {
        if (!empty(config('database.connections.tenant'))) {
            return 'tenant';
        }
        return config('database.default');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'company_id',
        'firstname',
        'middlename',
        'lastname',
        'email',
        'username',
        'type',
        'password',
        'address',
        'country',
        'country_code',
        'dial_code',
        'phone',
        'avatar',
        'created_by',
        'is_active',
        'is_online',
        'lang',
        'layout',
        'color_scheme',
        'layout_width',
        'layout_position',
        'topbar_color',
        'sidebar_size',
        'sidebar_view',
        'sidebar_color',
    ];

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }


    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'user_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'user_id');
    }

    public function family()
    {
        return $this->hasMany(UserFamilyInfo::class, 'user_id');
    }

    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function attendanceTimestamps()
    {
        return $this->hasMany(AttendanceTimestamp::class, 'user_id');
    }

    public function clientDetail()
    {
        return $this->hasOne(ClientDetail::class);
    }

    public function getNameAttribute()
    {
        return "$this->firstname $this->middlename $this->lastname";
    }
    public function getFullNameAttribute()
    {
        return $this->getNameAttribute();
    }

    public function getPhoneNumberAttribute()
    {
        return "$this->dial_code $this->phone";
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'type' => UserType::class,
        ];
    }

    public function hasVerifiedPhone()
    {
        return !empty($this->phone_verified_at);
    }

    public function evaluators()
    {
        // users assigned to evaluate this employee
        return $this->belongsToMany(User::class, 'employee_evaluator', 'employee_id', 'evaluator_id');
    }

    public function evaluatees()
    {
        return $this->belongsToMany(User::class, 'employee_evaluator', 'evaluator_id', 'employee_id');
    }

    /**
     * Gives locations permitted for the logged in user
     *
     * @return string or array
     */
    public function permitted_locations()
    {
        $user = $this;

        if ($user->can('access_all_locations')) {
            return 'all';
        } else {
            $business_id = request()->session()->get('user.business_id');
            $permitted_locations = [];
            $all_locations = \App\BusinessLocation::where('business_id', $business_id)->get();
            foreach ($all_locations as $location) {
                if ($user->can('location.' . $location->id)) {
                    $permitted_locations[] = $location->id;
                }
            }

            return $permitted_locations;
        }
    }
public static function forDropdown($business_id, $prepend_none = true, $include_commission_agents = false, $prepend_all = false)
    {
        $query = User::where('business_id', $business_id);
        // if (!$include_commission_agents) {
        //     $query->where('is_cmmsn_agnt', 0);
        // }// this condition is commented to include all users in dropdownby amanuel

        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(firstname, ''),' ',COALESCE(middlename, ''),' ',COALESCE(lastname,'')) as surename"));

        $users = $all_users->pluck('surename', 'id');

        //Prepend none
        if ($prepend_none) {
            $users = $users->prepend(__('lang_v1.none'), '');
        }

        //Prepend all
        if ($prepend_all) {
            $users = $users->prepend(__('lang_v1.all'), '');
        }

        return $users;
    }
    /**
     * Returns if a user can access the input location
     *
     * @param: int $location_id
     * @return boolean
     */
    public static function can_access_this_location($location_id)
    {
        $permitted_locations = auth()->user()->permitted_locations();

        if ($permitted_locations == 'all' || in_array($location_id, $permitted_locations)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is a system owner (has access to Superadmin module)
     *
     * @return boolean
     */
    public function isSystemOwner()
    {
        return $this->type === UserType::SUPERADMIN;
    }

    /**
     * Check if user is the tenant/business owner
     *
     * @return boolean
     */
    public function isTenantOwner()
    {
        if (!$this->business_id) {
            return false;
        }
        
        try {
            $business = \App\Business::on('mysql')->find($this->business_id);
            return $business && $business->owner_id === $this->id;
        } catch (\Exception $e) {
            \Log::warning('isTenantOwner check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is assigned to a specific company
     *
     * @return boolean
     */
    public function isCompanyUser()
    {
        return !empty($this->company_id);
    }
}
