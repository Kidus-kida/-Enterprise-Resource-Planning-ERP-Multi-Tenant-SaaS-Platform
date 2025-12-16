<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\ContactGroup;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Illuminate\Http\Request;
use Modules\Contacts\DataTables\ContactGroupDataTable;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ContactGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ContactGroupDataTable $dataTable)
    {
        $pageTitle = __("Contact Groups");
        return $dataTable->render('contacts::contact_groups.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $type = request()->type;
        $business_id = auth()->user()->business_id;
        
        // Fetch accounts for dropdowns
        $allAccounts = Account::where('account_type_id', 3)->get();
        $allAccountsType = AccountType::all();
        
        $price_groups = []; // SellingPriceGroup::forDropdown($business_id, false); 
        $user_groups = []; // User::forDropdown($business_id);
        
        $types = array('customer' => 'Customer', 'supplier' => 'Supplier', 'both' => 'Both (Customer & Supplier)');
        
        return view('contacts::contact_groups.create', compact('types', 'price_groups', 'type', 'allAccounts', 'allAccountsType', 'user_groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $input = $request->only(['name', 'amount', 'account_type_id', 'interest_account_id', 'type']);
            $input['business_id'] = auth()->user()->business_id;
            $input['created_by'] = auth()->id();
            
            // source logic typically formats amount, assuming input is valid number here
            $input['amount'] = !empty($input['amount']) ? $input['amount'] : 0;

            // Missing fields in tewoshr DB?
            // $input['price_type'] = $request->price_calculation_type;
            // $input['supplier_group_id'] = $request->selling_price_group_id;

            $contact_group = ContactGroup::create($input);

            // if($request->assigned_to){
            //     $obj = new UserContactAccess();
            //     $obj->contact_id = $contact_group->id;
            //     $obj->user_id = $request->assigned_to;
            //     $obj->save();
            // }

            $notification = notify(__('Contact Group created successfully'));
            return redirect()->route('contact-groups.index')->with($notification);

        } catch (\Exception $e) {
            \Log::error("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            
            return redirect()->back()->with('error', __('messages.something_went_wrong'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $business_id = auth()->user()->business_id;
        $contact_group = ContactGroup::findOrFail($id);
        
        $type = $contact_group->type;
        
        $allAccounts = Account::where('account_type_id', $contact_group->account_type_id)->get();
        $allAccountsType = AccountType::all();
        
        // Handling nulls if linked accounts are deleted or missing
        $selectedAccount = $contact_group->interest_account_id ? Account::find($contact_group->interest_account_id) : null;
        $selectedAccountType = $contact_group->account_type_id ? AccountType::find($contact_group->account_type_id) : null;
        
        $selectedSupGroupId = $contact_group->supplier_group_id ?? null;
        $price_groups = []; // SellingPriceGroup::forDropdown($business_id, false);
        $user_groups = []; // User::forDropdown($business_id);
        
        $types = array('customer' => 'Customer', 'supplier' => 'Supplier', 'both' => 'Both (Customer & Supplier)');
        
        return view('contacts::contact_groups.edit', compact('contact_group', 'types', 'price_groups', 'allAccounts', 'allAccountsType', 'selectedAccount', 'selectedAccountType', 'selectedSupGroupId', 'user_groups', 'type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $input = $request->only(['name', 'amount', 'account_type_id', 'interest_account_id', 'type']);
            // $input['business_id'] = auth()->user()->business_id;
            
            $contact_group = ContactGroup::findOrFail($id);
            
            $contact_group->name = $input['name'];
            $contact_group->type = $input['type'];
            $contact_group->amount = !empty($input['amount']) ? $input['amount'] : 0;
            $contact_group->account_type_id = $input['account_type_id'] ?? null;
            $contact_group->interest_account_id = $input['interest_account_id'] ?? null;
            
            // $contact_group->price_type = $request->price_calculation_type;
            // $contact_group->supplier_group_id = $request->selling_price_group_id;
            
            $contact_group->save();
            
            /*
            if( $request->assigned_to ){
                // Update UserContactAccess logic
            } else {
                // Delete logic
            }
            */

            $notification = notify(__('Contact Group updated successfully'));
            return redirect()->route('contact-groups.index')->with($notification);
        } catch (\Exception $e) {
            \Log::error("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            return redirect()->back()->with('error', __('messages.something_went_wrong'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $contact_group = ContactGroup::findOrFail($id);
            $contact_group->delete();
            
            $notification = notify(__('Contact Group deleted successfully'));
            return redirect()->route('contact-groups.index')->with($notification);
        } catch (\Exception $e) {
            \Log::error("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            return redirect()->back()->with('error', __('messages.something_went_wrong'));
        }
    }
    
    public function fetchAccount(Request $request)
    {
        $accounts = Account::where("account_type_id", $request->type_id)->get(["name", "id"]);
        return response()->json(['accounts' => $accounts]);
    }
}
