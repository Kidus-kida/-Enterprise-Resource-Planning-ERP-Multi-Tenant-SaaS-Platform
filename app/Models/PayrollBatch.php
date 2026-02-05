<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollBatch extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'period_start',
        'period_end',
        'pay_date',
        'status',
        'total_employees',
        'total_gross',
        'total_net',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
        'total_gross' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    /**
     * Get the details for this batch
     */
    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    /**
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate batch number
     */
    public static function generateBatchNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastBatch = self::where('batch_number', 'like', "PAY-{$year}{$month}%")
            ->orderBy('batch_number', 'desc')
            ->first();

        if ($lastBatch) {
            $lastNumber = (int)substr($lastBatch->batch_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PAY-{$year}{$month}-{$newNumber}";
    }
}

