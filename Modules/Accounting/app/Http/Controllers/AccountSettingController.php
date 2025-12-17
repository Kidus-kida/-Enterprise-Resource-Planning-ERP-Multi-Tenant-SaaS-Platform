<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\AccountSetting;

class AccountSettingController extends Controller
{
    public function index()
    {
        $business_id = session()->get('user.business_id');
        $setting = AccountSetting::where('business_id', $business_id)->where('key', 'default_accounts')->first();
        $defaults = $setting ? $setting->settings : [];
        
        $accounts = \Modules\Accounting\Models\Account::where('business_id', $business_id)
                        ->where('is_closed', 0)
                        ->pluck('name', 'id');

        return view('accounting::account-settings.index', compact('defaults', 'accounts'));
    }

    public function create()
    {
        return redirect()->route('account-settings.index');
    }

    public function store(Request $request)
    {
        try {
            $business_id = session()->get('user.business_id');
            
            // Define expected setting keys
            $data = $request->only([
                'default_sales_account', 
                'default_payable_account', 
                'default_receivable_account',
                'default_bank_account',
                'default_cash_account'
            ]);

            $setting = AccountSetting::firstOrNew([
                'business_id' => $business_id,
                'key' => 'default_accounts'
            ]);
            
            $setting->settings = $data;
            $setting->save();

            return redirect()->back()->with('success', __('Settings saved successfully'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to save settings: ') . $e->getMessage());
        }
    }

    public function show($id)
    {
       return redirect()->route('account-settings.index');
    }

    public function edit($id)
    {
        return redirect()->route('account-settings.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('account-settings.index');
    }

    public function destroy($id)
    {
        return redirect()->route('account-settings.index');
    }
}
