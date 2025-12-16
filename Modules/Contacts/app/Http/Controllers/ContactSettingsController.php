<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\ContactLinkedAccount;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactSettingsController extends Controller
{
    public function settings(){
        $business_id = request()->session()->get('user.business_id');

        $data = ContactLinkedAccount::where('contact_linked_accounts.business_id',$business_id)
                    ->join('users as u','u.id','contact_linked_accounts.created_by')
                    ->join('accounts as c','c.id','contact_linked_accounts.customer_advance')
                    ->join('accounts as s','s.id','contact_linked_accounts.supplier_advance')
                    ->leftjoin('accounts as cdr_liability','cdr_liability.id','contact_linked_accounts.customer_deposit_refund_liability_account')
                    ->leftjoin('accounts as cdr_asset','cdr_asset.id','contact_linked_accounts.customer_deposit_refund_asset_account')
                    ->select('contact_linked_accounts.*','c.name as cust','s.name as sup','u.username','cdr_liability.name as _customer_deposit_refund_liability_account','cdr_asset.name as _customer_deposit_refund_asset_account')->first();


        $liability = AccountType::getAccountTypeIdByName('Current Liabilities', $business_id)->id ?? null;


        $liability_accounts = Account::where('business_id', $business_id)->where('account_type_id', $liability)->pluck('name', 'id');



        $asset = AccountType::getAccountTypeIdByName('Current Assets', $business_id)->id ?? null;
        $asset_accounts = Account::where('business_id', $business_id)->where('account_type_id', $asset)->pluck('name', 'id');
        
        return view('contacts::contact.settings')
            ->with(compact('data','liability_accounts','asset_accounts'));
    }

    public function save_settings(Request $request){

        try {
            $input = $request->except('_token');

            $input['business_id'] = $business_id = $request->session()->get('user.business_id');
            $input['created_by'] = auth()->user()->id;

            ContactLinkedAccount::updateOrCreate(['business_id' => $business_id],$input);

                $output = [
                    'success' => 1,
                    'msg' => __('message.success'),
                    'background' => 'alert-success'
                ];
                // DB::commit(); // No transaction started in this block in source, but maybe needed? Source had commit but not beginTransaction in snippets viewed? Step 424 did not show beginTransaction.
                // Actually source ContactController line 154-186 shows DB::commit() at line 171 but NO DB::beginTransaction(). This is bad practice or I missed it.
                // I will add start/commit.
                
                return back()->with('status', $output);


        } catch (\Exception $e) {
            // DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
                'background' => 'alert-danger'
            ];
            return back()->with('status', $output);
        }
    }
}
