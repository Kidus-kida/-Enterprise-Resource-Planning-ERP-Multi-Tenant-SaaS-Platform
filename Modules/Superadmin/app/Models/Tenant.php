<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Superadmin\Database\Factories\TenantFactory;
use App\Business;

class Tenant extends Model
{
    use HasFactory;
    
    protected $connection = 'mysql';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'business_id',
        'database_name',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    protected static function newFactory(): TenantFactory
    {
        //return TenantFactory::new();
    }
}
