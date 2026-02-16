<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use LaravelLang\Routes\Events\LocaleHasBeenSetEvent;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(\App\Models\LeaveAccrualPlan::class, \App\Policies\LeaveAccrualPlanPolicy::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
        Event::listen(static function (LocaleHasBeenSetEvent $event) {
            $lang = $event->locale->code;
            Log::info('Locale set to: ' . $lang);
        });

        //Blade directive to format number into required format.
        \Blade::directive('num_format', function ($expression) {
            return "number_format((float)$expression, config('constants.currency_precision', 2), session('currency')['decimal_separator'] ?? '.', session('currency')['thousand_separator'] ?? ',')";
        });

        //Blade directive to format quantity values into required format.
        \Blade::directive('format_quantity', function ($expression) {
            return "number_format((float)$expression, config('constants.quantity_precision', 2), session('currency')['decimal_separator'] ?? '.', session('currency')['thousand_separator'] ?? ',')";
        });

        //Blade directive to format currency.
        \Blade::directive('format_currency', function ($expression) {
            return '<?php 
            $formated_number = "";
            if (session("business.currency_symbol_placement") == "before") {
                $formated_number .= session("currency")["symbol"] . " ";
            } 
            $formated_number .= number_format((float) ' . $expression . ', config("constants.currency_precision", 2) , session("currency")["decimal_separator"] ?? ".", session("currency")["thousand_separator"] ?? ",");

            if (session("business.currency_symbol_placement") == "after") {
                $formated_number .= " " . session("currency")["symbol"];
            }
            echo $formated_number; ?>';
        });

        //Blade directive to convert.
        \Blade::directive('format_date', function ($date) {
            return "<?php if(!empty($date)) { echo \Carbon\Carbon::createFromTimestamp(strtotime($date))->format(session('business.date_format') ?? 'Y-m-d'); } ?>";
        });

        \Blade::directive('format_time', function ($date) {
            return "<?php if(!empty($date)) { 
                \$time_format = session('business.time_format') == 24 ? 'H:i' : 'h:i A';
                echo \Carbon\Carbon::createFromTimestamp(strtotime($date))->format(\$time_format); 
            } ?>";
        });

        \Blade::directive('format_datetime', function ($date) {
            return "<?php if(!empty($date)) { 
                \$time_format = session('business.time_format') == 24 ? 'H:i' : 'h:i A';
                \$date_format = session('business.date_format') ?? 'Y-m-d';
                echo \Carbon\Carbon::createFromTimestamp(strtotime($date))->format(\$date_format . ' ' . \$time_format); 
            } ?>";
        });

        //Blade directive to display help text.
        \Blade::directive('show_tooltip', function ($message) {
            return "<?php
                echo '<i class=\"fa fa-info-circle text-info hover-q no-print \" aria-hidden=\"true\" 
                data-container=\"body\" data-toggle=\"popover\" data-placement=\"auto bottom\" 
                data-content=\"' . $message . '\" data-html=\"true\" data-trigger=\"hover\"></i>';
                ?>";
        });
    }
}
