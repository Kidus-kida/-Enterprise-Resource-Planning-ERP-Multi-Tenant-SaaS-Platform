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
        $date_format = session('business.date_format');
        $mysql_format = 'Y-m-d';
        if ($time) {
            if (session('business.time_format') == 12) {
                $date_format = $date_format . ' h:i A';
            } else {
                $date_format = $date_format . ' H:i';
            }
            $mysql_format = 'Y-m-d H:i:s';
        }


        return !empty($date_format) ? \Carbon::createFromFormat($date_format, $date)->format($mysql_format) : null;
    }

    public function uploadFile($request, $file_name, $dir_name, $file_type = 'document')
    {
        // If app environment is demo, return null
        if (config('app.env') == 'demo') {
            return null;
        }
    
        $uploaded_file_name = null;
        if ($request->hasFile($file_name) && $request->file($file_name)->isValid()) {
    
            if (!file_exists('./public/uploads/' . $dir_name)) {
                mkdir('./public/uploads/' . $dir_name, 0777, true);
            }
    
            // Check if mime type is image
            if ($file_type == 'image' && strpos($request->$file_name->getClientMimeType(), 'image/') === false) {
                throw new \Exception("Invalid image file");
            }
    
            if ($request->$file_name->getSize() <= config('constants.document_size_limit')) {
                $new_file_name = time() . '_' . $request->$file_name->getClientOriginalName();
    
                $uploaded_file_path = 'public/uploads/' . $dir_name . '/' . $new_file_name;
    
                if (strpos($request->$file_name->getClientMimeType(), 'image/') === true) {
                    // Convert the image to AVIF format
                    $img = Image::make($request->$file_name->getRealPath())->save($uploaded_file_path, null, function ($constraint) {
                        $constraint->format('avif');
                    });
                } else {
                    // Save the uploaded file without modification
                    $request->$file_name->move(public_path('uploads/' . $dir_name), $new_file_name);
                }
    
                $uploaded_file_name = $new_file_name;
            }
        }
    
        return $uploaded_file_name;
    }
    public function orderStatuses()
    {
        return ['received' => __('lang_v1.received'), 'pending' => __('lang_v1.pending'), 'ordered' => __('lang_v1.ordered')];
    }
}