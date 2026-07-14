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

    public static function resolveByIdentifier(string $identifier): ?self
    {
        $normalized = trim($identifier);
        if ($normalized === '') {
            return null;
        }

        $candidateIds = array_values(array_unique([
            $normalized,
            'tenant_' . $normalized,
            'tenant' . $normalized,
            \Illuminate\Support\Str::slug($normalized),
            \Illuminate\Support\Str::snake($normalized),
            \Illuminate\Support\Str::studly($normalized),
        ]));

        $candidateDatabaseNames = array_values(array_unique([
            $normalized,
            'tenant_' . $normalized,
            'tenant' . $normalized,
            \Illuminate\Support\Str::slug($normalized),
            \Illuminate\Support\Str::snake($normalized),
            \Illuminate\Support\Str::studly($normalized),
            $normalized . '_db',
            $normalized . '_database',
            'tenant_' . \Illuminate\Support\Str::slug($normalized),
        ]));

        return self::query()
            ->where(function ($query) use ($candidateIds, $candidateDatabaseNames) {
                $query->whereIn('id', $candidateIds)
                    ->orWhereIn('database_name', $candidateDatabaseNames)
                    ->orWhereHas('business', function ($businessQuery) use ($normalized) {
                        $businessQuery->where('subdomain', $normalized)
                            ->orWhere('tenant_id', $normalized)
                            ->orWhere('tenant_id', 'tenant_' . $normalized)
                            ->orWhere('tenant_id', 'tenant' . $normalized);
                    });
            })
            ->first();
    }

    protected static function newFactory(): TenantFactory
    {
        //return TenantFactory::new();
    }
}
