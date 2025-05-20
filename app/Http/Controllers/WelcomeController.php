<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Enums\UserType;
use App\Helpers\AppMenu;
use Illuminate\Support\Carbon;
use Modules\Sales\Models\Expense;
use Modules\Sales\Models\Invoice;
use LaravelLang\LocaleList\Locale;
use Modules\Sales\Models\Estimate;
use Modules\Accounting\Models\Budget;
use App\Http\Controllers\BaseController;

class WelcomeController extends BaseController
{

    public function index()
    {
        // dd("teka");
        return view('welcome', ["title" => "Home"]);
    }
}
