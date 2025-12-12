<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\AccountSetting;

class AccountSettingController extends Controller
{
    public function index()
    {
        $settings = AccountSetting::all();
        return view('accounting::account-settings.index', compact('settings'));
    }

    public function create()
    {
        return view('accounting::account-settings.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement store logic
        return redirect()->route('account-settings.index');
    }

    public function show($id)
    {
        $setting = AccountSetting::findOrFail($id);
        return view('accounting::account-settings.show', compact('setting'));
    }

    public function edit($id)
    {
        $setting = AccountSetting::findOrFail($id);
        return view('accounting::account-settings.edit', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement update logic
        return redirect()->route('account-settings.index');
    }

    public function destroy($id)
    {
        // TODO: Implement delete logic
        return redirect()->route('account-settings.index');
    }
}
