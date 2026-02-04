<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollTaxBracket extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'min_amount',
        'max_amount',
        'tax_rate',
        'order',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    /**
     * Calculate tax for a given amount using all brackets
     */
    public static function calculateTax($amount)
    {
        $brackets = self::orderBy('order')->get();
        $totalTax = 0;

        foreach ($brackets as $bracket) {
            if ($amount <= $bracket->min_amount) {
                break;
            }

            $taxableAmount = 0;
            
            if ($bracket->max_amount === null) {
                // Last bracket (no upper limit)
                $taxableAmount = $amount - $bracket->min_amount;
            } elseif ($amount > $bracket->max_amount) {
                // Amount exceeds this bracket
                $taxableAmount = $bracket->max_amount - $bracket->min_amount + 1;
            } else {
                // Amount falls within this bracket
                $taxableAmount = $amount - $bracket->min_amount + 1;
            }

            $totalTax += ($taxableAmount * $bracket->tax_rate / 100);
        }

        return round($totalTax, 2);
    }
}

