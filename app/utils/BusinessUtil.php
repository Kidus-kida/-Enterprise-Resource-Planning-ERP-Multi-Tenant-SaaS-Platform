<?php

namespace App\Utils;

use Modules\Contacts\Models\Transaction;

class BusinessUtil extends Util
{
    public function getFormNumber($type)
    {
        $business_id = request()->session()->get('business.id') ?? 1;
        $count = Transaction::where('business_id', $business_id)->where('type', $type)->count();
        $ref_no_prefixes = request()->session()->get('business.ref_no_prefixes');
        $ref_no_starting_numbers = request()->session()->get('business.ref_no_starting_number');
        if ($type == 'property_purchase') {
            $type = 'purchase';
        }
        $prefix =   !empty($ref_no_prefixes[$type]) ? $ref_no_prefixes[$type] : 'PO-';
        $starting_no =   !empty($ref_no_starting_numbers[$type]) ? $ref_no_starting_numbers[$type] : 1;

        $number = 1;
        if (!empty($starting_no)) {
            $number = (int) $starting_no + $count;
        }

        $form_no = $prefix . $number;

        return $form_no;
    }
}