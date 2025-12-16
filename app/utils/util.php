<?php

namespace App\Utils;

class Util
{
    public function num_uf($input_number, $currency_details = null)
    {
        $thousand_separator  = '';
        $decimal_separator  = '';

        if (!empty($currency_details)) {
            $thousand_separator = $currency_details->thousand_separator;
            $decimal_separator = $currency_details->decimal_separator;
        } else {
            $thousand_separator = session()->has('currency') ? session('currency')['thousand_separator'] : '';
            $decimal_separator = session()->has('currency') ? session('currency')['decimal_separator'] : '';
        }

        $num = str_replace($thousand_separator, '', $input_number);
        $num = str_replace($decimal_separator, '.', $num);

        return (float)$num;
    }

    public function uf_date($date, $time = false)
    {
        try {
            if (empty($date)) {
                return null;
            }

            $date_format = session('business.date_format', 'd/m/Y');
            $mysql_format = 'Y-m-d';
            
            if ($time) {
                if (session('business.time_format') == 12) {
                    $date_format = $date_format . ' h:i A';
                } else {
                    $date_format = $date_format . ' H:i';
                }
                $mysql_format = 'Y-m-d H:i:s';
            }

            return \Carbon\Carbon::createFromFormat($date_format, $date)->format($mysql_format);
        } catch (\Exception $e) {
            // If parsing fails, try direct carbon parse
            return \Carbon\Carbon::parse($date)->format($time ? 'Y-m-d H:i:s' : 'Y-m-d');
        }
    }

    public function uploadFile($request, $file_name, $dir_name, $file_type = 'document')
    {
        // If app environment is demo, return null
        if (config('app.env') == 'demo') {
            return null;
        }
    
        $uploaded_file_name = null;
        if ($request->hasFile($file_name) && $request->file($file_name)->isValid()) {
    
            if (!file_exists(public_path('uploads/' . $dir_name))) {
                mkdir(public_path('uploads/' . $dir_name), 0777, true);
            }
    
            // Check if mime type is image
            if ($file_type == 'image' && strpos($request->$file_name->getClientMimeType(), 'image/') === false) {
                throw new \Exception("Invalid image file");
            }
    
            $max_size = config('constants.document_size_limit', 5000000); // 5MB default
            if ($request->$file_name->getSize() <= $max_size) {
                $new_file_name = time() . '_' . $request->$file_name->getClientOriginalName();
    
                $uploaded_file_path = 'uploads/' . $dir_name . '/' . $new_file_name;
    
                $request->$file_name->move(public_path('uploads/' . $dir_name), $new_file_name);
    
                $uploaded_file_name = $new_file_name;
            }
        }
    
        return $uploaded_file_name;
    }
    
    public function orderStatuses()
    {
        return ['received' => __('lang_v1.received'), 'pending' => __('lang_v1.pending'), 'ordered' => __('lang_v1.ordered')];
    }

    public function num_f($input_number, $precision = 2, $currency_details = null)
    {
        $thousand_separator = '';
        $decimal_separator = '';

        if (!empty($currency_details)) {
            $thousand_separator = $currency_details->thousand_separator;
            $decimal_separator = $currency_details->decimal_separator;
        } else {
            $thousand_separator = session()->has('currency') ? session('currency')['thousand_separator'] : ',';
            $decimal_separator = session()->has('currency') ? session('currency')['decimal_separator'] : '.';
        }

        return number_format($input_number, $precision, $decimal_separator, $thousand_separator);
    }

    public function format_date($date, $show_time = false, $business_details = null)
    {
        $format = 'd/m/Y';
        
        if (!empty($business_details)) {
            $format = $business_details->date_format;
        } else if (session()->has('business.date_format')) {
            $format = session('business.date_format');
        }

        if (!empty($date)) {
            $date_obj = \Carbon\Carbon::parse($date);
            
            if ($show_time) {
                $time_format = session()->has('business.time_format') && session('business.time_format') == 12 ? 'h:i A' : 'H:i';
                return $date_obj->format($format . ' ' . $time_format);
            }
            
            return $date_obj->format($format);
        }

        return '';
    }

    public function setAndGetReferenceCount($type, $business_id = null)
    {
        if (empty($business_id)) {
            $business_id = request()->session()->get('user.business_id', 1);
        }

        // Simple increment counter - create ReferenceCount model or just count transactions
        $count = \Modules\Contacts\Models\Transaction::where('business_id', $business_id)
            ->where('type', $type)
            ->count();
        
        return $count + 1;
    }

    public function generateReferenceNumber($type, $ref_count, $business_id = null, $default_prefix = null)
    {
        if (empty($business_id)) {
            $business_id = request()->session()->get('user.business_id', 1);
        }

        $prefix = !empty($default_prefix) ? $default_prefix : strtoupper(substr($type, 0, 2));
        
        return $prefix . '-' . str_pad($ref_count, 4, '0', STR_PAD_LEFT);
    }

    public function payment_types($location_id = null, $show_all = true, $show_labels = true, $for_modal = false, $show_balance = false, $for_purchase = false, $enabled_field = null)
    {
        $payment_types = [
            'cash' => 'Cash',
            'card' => 'Card',
            'cheque' => 'Che que',
            'bank_transfer' => 'Bank Transfer',
            'other' => 'Other',
        ];

        if ($for_purchase) {
            $payment_types['credit_purchase'] = 'Credit Purchase';
            unset($payment_types['card']);
        }

        return $payment_types;
    }

    public function get_review($start_date = null, $end_date = null, $business_id = null, $is_petro = true)
    {
        // Placeholder - implement based on your review system
        // Return empty array for now to not block purchases
        return [];
    }
}