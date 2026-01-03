<?php

namespace Modules\Superadmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Superadmin\Database\Factories\DomainFactory;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'tenant_id'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function newFactory(): DomainFactory
    {
        //return DomainFactory::new();
    }
}
