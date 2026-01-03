<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Modules\Accounting\Models\AccountSetting;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountGroup;
use Illuminate\Support\Facades\Auth;

class AccountSettingController extends Controller
{


    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {

    //         $business_id = session()->get('user.business_id');

    //         $query = AccountSetting::query()
    //             // ->where('business_id', $business_id)
    //             ->orderBy('created_at', 'desc');

    //         return DataTables::of($query)
    //             ->addIndexColumn()

    //             ->addColumn('type', function ($row) {
    //                 return $row->key === 'default_accounts'
    //                     ? '<span class="badge bg-info">Default Accounts</span>'
    //                     : '<span class="badge bg-success">Manual Entry</span>';
    //             })

    //             ->addColumn('date', function ($row) {
    //                 return $row->date ?? '-';
    //             })

    //             ->addColumn('account', function ($row) {
    //                 if ($row->account_id) {
    //                     return Account::find($row->account_id)->name ?? '-';
    //                 }
    //                 return '-';
    //             })

    //             ->addColumn('group', function ($row) {
    //                 if ($row->group_id) {
    //                     return AccountGroup::find($row->group_id)->name ?? '-';
    //                 }
    //                 return '-';
    //             })

    //             ->addColumn('amount', function ($row) {
    //                 return $row->amount
    //                     ? number_format($row->amount, 2)
    //                     : '-';
    //             })

    //             ->addColumn('settings', function ($row) {
    //                 if ($row->settings) {
    //                     $html = '<ul class="mb-0">';
    //                     foreach ($row->settings as $key => $value) {
    //                         $html .= "<li><strong>{$key}</strong>: {$value}</li>";
    //                     }
    //                     $html .= '</ul>';
    //                     return $html;
    //                 }
    //                 return '-';
    //             })

    //             ->addColumn('action', function ($row) {

    //                 $editUrl = route('account-settings.edit', $row->id);
    //                 $deleteUrl = route('account-settings.destroy', $row->id);

    //                 return '
    //             <div class="dropdown dropdown-action">
    //                 <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown">
    //                     <i class="material-icons">more_vert</i>
    //                 </a>
    //                 <div class="dropdown-menu dropdown-menu-right">
    //                     <a class="dropdown-item"
    //                         data-url="' . $editUrl . '"
    //                         href="javascript:void(0)"
    //                         data-ajax-modal="true"
    //                         data-size="md"
    //                         data-title="Edit Account Setting">
    //                         <i class="fa-solid fa-pencil m-r-5"></i> Edit
    //                     </a>

    //                     <a class="dropdown-item" href="javascript:void(0)"
    //                        onclick="deleteAccountSetting(' . $row->id . ')">
    //                         <i class="fa-solid fa-trash m-r-5"></i> Delete
    //                     </a>
    //                 </div>
    //             </div>';
    //             })

    //             ->rawColumns(['type', 'settings', 'action'])
    //             ->make(true);
    //     }

    //     return view('accounting::account-settings.index');
    // }


    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');

            // $settings = AccountSetting::where('business_id', $business_id);
            $settings = AccountSetting::orderBy('created_at', 'desc');

            return DataTables::of($settings)
                ->addIndexColumn()

                ->addColumn('type', function ($row) {
                    return $row->key === 'default_accounts'
                        ? '<span class="badge bg-primary">Default Accounts</span>'
                        : '<span class="badge bg-success">Manual Entry</span>';
                })

                ->addColumn('date', fn($row) => $row->date ?? '-')

                ->addColumn('account', function ($row) {
                    return $row->account_id
                        ? optional(Account::find($row->account_id))->name
                        : '-';
                })

                ->addColumn('group', function ($row) {
                    return $row->group_id
                        ? optional(\Modules\Accounting\Models\AccountGroup::find($row->group_id))->name
                        : '-';
                })

                ->addColumn(
                    'amount',
                    fn($row) =>
                    $row->amount ? number_format($row->amount, 2) : '-'
                )

                ->addColumn('settings', function ($row) {

                    if (!$row->settings) {
                        return '-';
                    }

                    $settings = $row->settings;

                    $count = count($settings);

                    // Build tooltip content with account names
                    $tooltip = '';

                    foreach ($settings as $key => $accountId) {
                        $account = Account::find($accountId);
                        $tooltip .= '<div><strong>' .
                            ucwords(str_replace('_', ' ', $key)) .
                            '</strong>: ' .
                            ($account->name ?? '-') .
                            '</div>';
                    }

                    return '
                    <span class="badge bg-info text-dark"
                          data-bs-toggle="popover"
                          data-bs-html="true"
                          data-bs-content="' . e($tooltip) . '">
                        ' . $count . ' Settings
                    </span>
                ';
                })

                ->addColumn('action', function ($row) {
                    return '
                <div class="dropdown dropdown-action">
                    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="material-icons">more_vert</i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item"
                           data-url="' . route('account-settings.edit', $row->id) . '"
                           href="javascript:void(0)"
                           data-ajax-modal="true"
                           data-size="md">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        
                        <a class="dropdown-item"
                           href="javascript:void(0)"
                           onclick="deleteAccountSetting(' . $row->id . ')">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </div>
                </div>';
                })

                ->rawColumns(['type', 'settings', 'action'])
                ->make(true);
        }

        return view('accounting::account-settings.index');
    }



    public function create()
    {
        $business_id = session()->get('user.business_id');
        $setting = AccountSetting::where('business_id', $business_id)->where('key', 'default_accounts')->first();
        $defaults = $setting ? $setting->settings : [];

        $accounts = \Modules\Accounting\Models\Account::where('business_id', $business_id)
            ->where('is_closed', 0)
            ->pluck('name', 'id');
        $account_groups =  \Modules\Accounting\Models\AccountGroup::all()->pluck('name', 'id');

        return view('accounting::account-settings.create', compact('defaults', 'accounts', 'account_groups'));
    }


    public function store(Request $request)
    {
        // Determine business_id safely
        $business_id = session()->get('user.business_id') ? auth()->user()->business_id : auth()->id();
        // If the form includes default account settings, save/update them
        if ($request->has('settings')) {
            AccountSetting::updateOrCreate(
                [
                    'business_id' => $business_id,
                    'key' => 'default_accounts',
                ],
                [
                    'settings' => $request->settings,
                    'created_by' => auth()->id(),
                ]
            );
        }

        // If the form includes a manual entry, save it
        if ($request->filled('key') && $request->key === 'manual_entry') {
            $request->validate([
                'date' => 'required|date',
                'account_id' => 'required|integer',
                'amount' => 'required|numeric',
                'group_id' => 'nullable|integer',
                'at_asset_id' => 'nullable|integer',
                'at_obe_id' => 'nullable|integer',
            ]);

            AccountSetting::create([
                'business_id' => $business_id,
                'key' => $request->key,
                'date' => $request->date,
                'account_id' => $request->account_id,
                'group_id' => $request->group_id,
                'amount' => $request->amount,
                'at_asset_id' => $request->at_asset_id,
                'at_obe_id' => $request->at_obe_id,
                'created_by' => auth()->id(),
            ]);
        }

        return back()->with('success', __('Settings and/or Entry saved successfully'));
    }



    public function show($id)
    {
        return redirect()->route('account-settings.index');
    }

    public function edit($id)
    {
        $business_id = session()->get('user.business_id') ?? auth()->id();

        // Main setting record
        $setting = AccountSetting::where('business_id', $business_id)
            ->findOrFail($id);

        // Decode settings safely (important)
        $settings = is_array($setting->settings)
            ? $setting->settings
            : json_decode($setting->settings, true);

        // Accounts list
        $accounts = \Modules\Accounting\Models\Account::pluck('name', 'id');

        // Account groups list
        $account_groups = \Modules\Accounting\Models\AccountGroup::
            pluck('name', 'id');

        return view('accounting::account-settings.create', compact(
            'setting',
            'settings',
            'accounts',
            'account_groups'
        ));
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
