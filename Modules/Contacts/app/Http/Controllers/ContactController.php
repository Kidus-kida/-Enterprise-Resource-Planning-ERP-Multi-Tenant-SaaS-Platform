<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Customer;
use Modules\Contacts\Models\Media;
use Modules\Contacts\Models\AccountTransaction;
use Illuminate\Http\Request;
use Modules\Contacts\Models\UserContactAccess;
use App\DataTables\ContactDataTable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Modules\Contacts\Models\TransactionPayment;
use Modules\Contacts\Models\NotificationTemplate;
use Modules\Contacts\ModelsReferenceCount;
use App\Models\User;
use Carbon\Carbon;

use Modules\Contacts\Models\Vehicle;

use Modules\Contacts\Models\ContactGroup;
use Modules\Contacts\Models\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;


class ContactController extends Controller                   
{
    protected $moduleUtil;
    protected $commonUtil;
    protected $transactionUtil;
    protected $businessUtil;
  public function __construct(
    ModuleUtil $moduleUtil,
    TransactionUtil $transactionUtil,
    BusinessUtil $businessUtil
) {
    $this->moduleUtil = $moduleUtil;
    $this->transactionUtil = $transactionUtil;
    $this->businessUtil = $businessUtil; 
}


    /**
     * Display a listing of the resource.
     */
   
    public function index(ContactDataTable $dataTable)
    {
 $pageTitle = __("Contacts");
        $type = request()->get('type');
        $types = ['supplier', 'customer'];
        $business_id = request()->session()->get('user.business_id');
        if (empty($type) || !in_array($type, $types)) {
            return redirect()->back();
        }

        if (request()->ajax()) {
            return $type == 'supplier' ? $this->indexSupplier() : ($type == 'customer' ? $this->indexCustomer() : abort(404));
        }
        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && $type == 'customer');

        // Get contact fields from session or set to empty array
        $contact_fields = session('business.contact_fields', []);

        // Get user groups for dropdown
        $user_groups = User::forDropdown($business_id);

        // Check if it's a property customer
        $is_property = isset($is_property_customer);

        // Check customer code and get contact ID
         $contact_id = $this->businessUtil->check_customer_code($business_id);
return $dataTable->render('pages.contacts.index', compact('type', 'reward_enabled', 'contact_fields', 'is_property', 'user_groups','pageTitle'));
    


       
    }

    /**
     * Show the form for creating a new resource.
     */
   
 public function create()
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }
        $mode = request()->mode;
        $type = request()->type;
        $business_id = request()->session()->get('user.business_id');
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }
        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = ContactGroup::forDropdown($business_id);
        $supplier_groups = ContactGroup::forDropdown($business_id, true, false, 'supplier');
        $contact_id = $this->businessUtil->check_customer_code($business_id);
        $user_groups = User::forDropdown($business_id);

        if($type == 'customer'){
            $notifications = NotificationTemplate::customerNotifications();
        }else{
            $notifications = NotificationTemplate::supplierNotifications();
        }
        
        $customers = Contact::customersDropdown($business_id, false);

        return view('pages.contacts.create')
            ->with(compact('notifications','types','customers', 'customer_groups', 'supplier_groups', 'contact_id', 'type','user_groups', 'mode'));
    }
    /**
     * Store a newly created resource in storage.
     */
   
//     public function store(Request $request)
// {

//     if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
//         abort(403, 'Unauthorized action.');
//     }

// //  Validation rules
//     $rules = [
//         'name' => 'required|string|max:255',
//         'contact_type' => 'required|string',
//         'mobile' => 'required|string|max:255',
//     ];

//     if (in_array($request->contact_type, ['customer', 'both'])) {
//         $rules['password'] = 'required|min:4|max:255';
//         $rules['confirm_password'] = 'required|same:password';
//     }

//     $request->validate($rules);

//     try {
//         DB::beginTransaction();

//         //  Business context
//         $business_id = session()->get('user.business_id');

//         // 📦 Subscription check
//         if (!$this->moduleUtil->isSubscribed($business_id)) {
//             return $this->moduleUtil->expiredResponse();
//         }

//         //  Customer quota check
//         if ($request->contact_type == 'customer') {
//             if (!$this->moduleUtil->isQuotaAvailable('customers', $business_id)) {
//                 return $this->moduleUtil
//                     ->quotaExpiredResponse('customers', $business_id, action('ContactController@index'));
//             }
//         }

//         /* ------------------------------------------------
//          |  CREATE CUSTOMER USER
//          ------------------------------------------------ */
//         if ($request->contact_type == 'customer') {
//             Customer::create([
//                 'business_id' => $business_id,
//                 'first_name' => $request->name,
//                 'last_name' => '',
//                 'email' => $request->email,
//                 'username' => $request->contact_id ?? $request->name,
//                 'password' => Hash::make($request->password),
//                 'mobile' => $request->mobile ?? ' ',
//                 'contact_number' => $request->alternate_mobile,
//                 'geo_location' => $request->country ?? ' ',
//                 'address' => $request->address ?? ' ',
//                 'town' => $request->state ?? ' ',
//                 'district' => $request->city ?? ' ',
//                 'is_company_customer' => 1
//             ]);
//         }

//         /* ------------------------------------------------
//          | 2️⃣ PREPARE CONTACT DATA
//          ------------------------------------------------ */
//         $input = $request->only([
//             'name','email','mobile','landline','alternate_mobile',
//             'city','state','country','address','landmark',
//             'supplier_group_id','customer_group_id','nic_number'
//         ]);

//         $input['type'] = $request->contact_type;
//         $input['business_id'] = $business_id;
//         $input['created_by'] = auth()->id();

//         // VAT & alternate mobile mapping
//         $input['vat_number'] = $request->vat_no ?? null;
//         $input['alternate_number'] = $request->alternate_mobile ?? null;
//         $input['should_notify'] = $request->send_sms ?? 0;

//         // 🆔 Contact ID check & generation
//         if (!empty($request->contact_id)) {
//             $exists = Contact::where('business_id', $business_id)
//                 ->where('contact_id', $request->contact_id)
//                 ->exists();

//             if ($exists) {
//                 throw new \Exception('Duplicate Contact ID');
//             }
//             $input['contact_id'] = $request->contact_id;
//         } else {
//             $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts');
//             $input['contact_id'] = $this->commonUtil
//                 ->generateReferenceNumber('contacts', $ref_count);
//         }

//         /* ------------------------------------------------
//          | 3️⃣ FILE UPLOADS (Media)
//          ------------------------------------------------ */
//         if ($request->hasFile('image')) {
//             $input['image'] = Media::uploadFile($request->file('image'));
//         }

//         if ($request->hasFile('signature')) {
//             $input['signature'] = Media::uploadFile($request->file('signature'));
//         }

//         /* ------------------------------------------------
//          | 4️⃣ CREATE CONTACT
//          ------------------------------------------------ */
//         $contact = Contact::create($input);

//         /* ------------------------------------------------
//          | 5️⃣ VEHICLE CREATION
//          ------------------------------------------------ */
//         if ($request->filled('vehicle_no')) {
//             Vehicle::create([
//                 'customer_id' => $contact->id,
//                 'vehicle_no' => $request->vehicle_no
//             ]);
//         }

//         /* ------------------------------------------------
//          | 6️⃣ NOTIFICATION CONTACTS
//          ------------------------------------------------ */
//         if ($request->filled('notification_parameters')) {
//             $contact->notification_contacts = json_encode(
//                 json_decode($request->notification_parameters)
//             );
//             $contact->save();
//         }

//         /* ------------------------------------------------
//          | 7️⃣ ASSIGN USER TO CONTACT
//          ------------------------------------------------ */
//         if ($request->assigned_to) {
//             UserContactAccess::create([
//                 'contact_id' => $contact->id,
//                 'user_id' => $request->assigned_to
//             ]);
//         }

//         /* ------------------------------------------------
//          | 8️⃣ OPENING BALANCE (ERP-CORRECT)
//          ------------------------------------------------ */
//         if ($request->filled('opening_balance')) {
//             $this->transactionUtil->createOpeningBalanceTransaction(
//                 $business_id,
//                 $contact->id,
//                 $request->opening_balance,
//                 $request->transaction_date
//             );
//         }

//         DB::commit();

//         return redirect()
//             ->route('contacts.index')
//             ->with(notify(__('Contact added successfully')));

//     } catch (\Exception $e) {
//         DB::rollBack();
//         Log::error($e);

//         return back()
//             ->withInput()
//             ->with(notify(__('Something went wrong: ') . $e->getMessage(), 'error'));
//     }
// }
public function store(Request $request)
{
    if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
        abort(403, 'Unauthorized action.');
    }

    // Validation rules
    $rules = [
        'name' => 'required|string|max:255',
        'contact_type' => 'required|string',
        'mobile' => 'required|string|max:255',
    ];

    if (in_array($request->contact_type, ['customer', 'both'])) {
        $rules['password'] = 'required|min:4|max:255';
        $rules['confirm_password'] = 'required|same:password';
    }

    $request->validate($rules);

    try {
        DB::beginTransaction();

        // 🔹 Safe business ID
        $business_id = session()->get('user.business_id') ?? auth()->user()->business_id;

        if (!$business_id) {
            return back()->withInput()->with(notify(__('Business ID not found'), 'error'));
        }

        // 📦 Subscription check
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        // Customer quota check
        if ($request->contact_type == 'customer') {
            if (!$this->moduleUtil->isQuotaAvailable('customers', $business_id)) {
                return $this->moduleUtil
                    ->quotaExpiredResponse('customers', $business_id, action('ContactController@index'));
            }
        }

        // CREATE CUSTOMER USER
        if ($request->contact_type == 'customer') {
            Customer::create([
                'business_id' => $business_id,
                'first_name' => $request->name,
                'last_name' => '',
                'email' => $request->email,
                'username' => $request->contact_id ?? $request->name,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile ?? ' ',
                'contact_number' => $request->alternate_mobile,
                'geo_location' => $request->country ?? ' ',
                'address' => $request->address ?? ' ',
                'town' => $request->state ?? ' ',
                'district' => $request->city ?? ' ',
                'is_company_customer' => 1
            ]);
        }

        // PREPARE CONTACT DATA
        $input = $request->only([
            'name','email','mobile','landline','alternate_mobile',
            'city','state','country','address','landmark',
            'supplier_group_id','customer_group_id','nic_number'
        ]);

        $input['type'] = $request->contact_type;
        $input['business_id'] = $business_id;
        $input['created_by'] = auth()->id();

        // VAT & alternate mobile mapping
        $input['vat_number'] = $request->vat_no ?? null;
        $input['alternate_number'] = $request->alternate_mobile ?? null;
        $input['should_notify'] = $request->send_sms ?? 0;

        // Contact ID check & generation
        if (!empty($request->contact_id)) {
            $exists = Contact::where('business_id', $business_id)
                ->where('contact_id', $request->contact_id)
                ->exists();

            if ($exists) {
                throw new \Exception('Duplicate Contact ID');
            }
            $input['contact_id'] = $request->contact_id;
        } else {
            $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts');
            $input['contact_id'] = $this->commonUtil
                ->generateReferenceNumber('contacts', $ref_count);
        }

        // FILE UPLOADS (Media)
        if ($request->hasFile('image')) {
            $input['image'] = Media::uploadFile($request->file('image'));
        }

        if ($request->hasFile('signature')) {
            $input['signature'] = Media::uploadFile($request->file('signature'));
        }

        // CREATE CONTACT
        $contact = Contact::create($input);

        // VEHICLE CREATION
        if ($request->filled('vehicle_no')) {
            Vehicle::create([
                'customer_id' => $contact->id,
                'vehicle_no' => $request->vehicle_no
            ]);
        }

        // NOTIFICATION CONTACTS
        if ($request->filled('notification_parameters')) {
            $contact->notification_contacts = json_encode(
                json_decode($request->notification_parameters)
            );
            $contact->save();
        }

        // ASSIGN USER TO CONTACT
        if ($request->assigned_to) {
            UserContactAccess::create([
                'contact_id' => $contact->id,
                'user_id' => $request->assigned_to
            ]);
        }

        // OPENING BALANCE
        if ($request->filled('opening_balance')) {
            $this->transactionUtil->createOpeningBalanceTransaction(
                $business_id,
                $contact->id,
                $request->opening_balance,
                $request->transaction_date
            );
        }

        DB::commit();

        return redirect()
            ->route('contacts.index')
            ->with(notify(__('Contact added successfully')));

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error($e);

        return back()
            ->withInput()
            ->with(notify(__('Something went wrong: ') . $e->getMessage(), 'error'));
    }
}



    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Simple show, or redirect to edit
        // $contact = Contact::findOrFail($id);
        // return view('pages.contacts.show', compact('contact'));
    }

   
     public function edit($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact = Contact::leftjoin('user_contact_access','contacts.id','user_contact_access.contact_id')
            ->leftjoin('users','user_contact_access.user_id','users.id')
            ->select([
                    'contacts.*',
                    'user_contact_access.user_id'
                    ])
            ->where('contacts.business_id', $business_id)->find($id);
             if (empty($contact)) {
            abort(404, 'Contact not found');
        }
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }
            $types = [];
            if (auth()->user()->can('supplier.create')) {
                $types['supplier'] = __('report.supplier');
            }
            if (auth()->user()->can('customer.create')) {
                $types['customer'] = __('report.customer');
            }
            if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
                $types['both'] = __('lang_v1.both_supplier_customer');
            }
            $customer_groups = ContactGroup::forDropdown($business_id);
            $supplier_groups = ContactGroup::forDropdown($business_id, true, false, 'supplier');
            $ob_transaction =  Transaction::where('contact_id', $id)
                ->where('type', 'opening_balance')
                ->first();
            $opening_balance = !empty($ob_transaction->final_total) ? $ob_transaction->final_total : 0;
            //Deduct paid amount from opening balance.
            if (!empty($opening_balance)) {
                $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                if (!empty($opening_balance_paid)) {
                    $opening_balance = $opening_balance - $opening_balance_paid;
                }
                $opening_balance = $this->commonUtil->num_f($ob_transaction->final_total);
            }

            if($contact->type == 'customer'){
                $notifications = NotificationTemplate::customerNotifications();
            }else{
                $notifications = NotificationTemplate::supplierNotifications();
            }
            
            $customers = Contact::customersDropdown($business_id, false);

            $user_groups = User::forDropdown($business_id);
            $contact_id = $this->businessUtil->check_customer_code($business_id);
            return view('pages.contacts.edit')
                ->with(compact('notifications','contact','customers', 'types', 'customer_groups', 'supplier_groups', 'opening_balance', 'ob_transaction','user_groups', 'contact_id'));
        }
    }


   
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }
        if(request()->ajax()) {
            try {                                                                              // removed below line
                $input = $request->only(['address_2','address_3','sub_customer','sub_customers','vat_number','address','credit_notification','transaction_date','should_notify','contact_id','nic_number', 'type', 'supplier_business_name', 'name', 'tax_number', 'pay_term_number', 'pay_term_type', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country', 'landmark', 'customer_group_id', 'supplier_group_id', 'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'email']);
                $input['contact_transaction_date'] = $this->transactionUtil->uf_date($input['transaction_date']);
                unset($input['transaction_date']);
            
                $input['sub_customers'] = json_encode($request->sub_customers ?? []);
                $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;
                $business_id = $request->session()->get('user.business_id');
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                }
                $contact_user = User::where('username', $input['contact_id'])->first();
                if (request()->type == 'customer') {

                    if($request->hasFile('image')){
                        $imageName = Media::uploadFile($request->file('image'));
                        $input['image']=$imageName;
                    }if($request->hasFile('signature')){
                        $signatureName = Media::uploadFile($request->file('signature'));
                        $input['signature']=$signatureName;
                    }

                    if (!empty(request()->password)) {
                        $validator = Validator::make(request()->all(), [
                            'password' => 'required|min:4|max:255',
                            'confirm_password' => 'required|same:password'
                        ]);
                        if ($validator->fails()) {
                            $output = [
                                'success' => false,
                                'msg' => 'Password does not match'
                            ];
                            return $output;
                        }
                    }
                    if (empty($contact_user)) {
                        if (!$this->moduleUtil->isQuotaAvailable('customers', $business_id)) {
                            return $this->moduleUtil->quotaExpiredResponse('customers', $business_id, action('ContactController@index'));
                        }
                        // it is company customer
                        $customer_details = request()->only(['email', 'password']);
                        $customer_details['language'] = env('APP_LOCALE');
                        $customer_details['surname'] = '';
                        $customer_details['first_name'] = request()->name;
                        $customer_details['last_name'] = '';
                        $customer_details['username'] = request()->contact_id;
                        $customer_details['is_customer'] = 1;
                        $customer_details['business_id'] = $business_id;
                        if (!empty(request()->password)) {
                            $customer_details['password'] = Hash::make(request()->password);
                        }
                        $user = User::create_user($customer_details);
                        $user->business_id = $business_id;
                        $user->is_customer = 1;
                        $enable_customer_login = System::getProperty('enable_customer_login');
                        if (!$enable_customer_login) {
                            $user->status = 'inactive';
                        }
                        $user->save();
                    } else {
                        $contact_user->first_name = request()->name;
                        if (!empty(request()->password)) {
                            $contact_user->password = Hash::make(request()->password);
                        }
                        $contact_user->save();
                    }
                }
                $count = 0;
                //Check Contact id
                if (!empty($input['contact_id'])) {
                    $count = Contact::where('business_id', $business_id)
                        ->where('contact_id', $input['contact_id'])
                        ->where('id', '!=', $id)
                        ->count();
                }
                if ($count == 0) {
                    $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                    foreach ($input as $key => $value) {
                        $contact->$key = $value;
                    }
                    $contact->save();
                    //update data of user access table
                    if( $request->assigned_to ){
                        $user_contact_access = UserContactAccess::updateOrCreate(
                                    ['contact_id' => $id],
                                    ['user_id' => $request->assigned_to, 'contact_id' => $id]
                                );

                    }else{

                        UserContactAccess::where('contact_id',$id)->delete();
                    }
                    //Get opening balance if exists
                    $ob_transaction =  Transaction::where('contact_id', $id)
                        ->where('type', 'opening_balance')
                        ->first();
                    if (!empty($ob_transaction)) {
                        $amount = $this->commonUtil->num_uf($request->input('opening_balance'));
                        // $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                        // if (!empty($opening_balance_paid)) {
                        //     $amount -= $opening_balance_paid;
                        // }
                        $ob_transaction->final_total = $amount;
                        $ob_transaction->total_before_tax = $amount;
                        $ob_transaction->transaction_date = $this->transactionUtil->uf_date($request->transaction_date);
                        $ob_transaction->save();
                        $payable_account_id = $this->transactionUtil->account_exist_return_id('Accounts Payable');
                        $receivealbe_account_id = $this->transactionUtil->account_exist_return_id('Accounts Receivable');
                        $account_transaction = AccountTransaction::where('transaction_id', $ob_transaction->id)->whereIn('account_id', [$payable_account_id, $receivealbe_account_id])->first();
                        
                        if(!empty($account_transaction)){
                            $account_transaction->amount = $ob_transaction->final_total;
                            $account_transaction->save();
                        }
                        
                        $contact_ledger_trnsaction = ContactLedger::where('transaction_id', $ob_transaction->id)->first();
                        $contact_ledger_trnsaction->amount = $ob_transaction->final_total;
                        $contact_ledger_trnsaction->save();
                        //Update opening balance payment status
                        $this->transactionUtil->updatePaymentStatus($ob_transaction->id, $ob_transaction->final_total);
                    } else {
                        //Add opening balance
                        if (!empty($request->input('opening_balance'))) {
                            $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $request->input('opening_balance'));
                        }
                    }

                    $notification_parameters = json_decode($request->notification_parameters);
                    $contact->notification_contacts = json_encode($notification_parameters);
                    $contact->save();

                    $output = [
                        'success' => true,
                        'msg' => __("contact updated successfully")
                    ];
                } else {
                    throw new \Exception("Error Processing Request", 1);
                }
            } catch (\Exception $e) {
                Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
                $output = [
                    'success' => false,
                    'msg' => __("something went wrong")
                ];
            }
            return $output;
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('supplier.delete') && !auth()->user()->can('customer.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;
                //Check if any transaction related to this contact exists
                $count = Transaction::where('business_id', $business_id)
                    ->where('contact_id', $id)->where('final_total', '>', 0)
                    ->count();
                if ($count == 0) {
                    $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                    $transactions = Transaction::where('business_id', $business_id)
                        ->where('contact_id', $id)->get();
                    foreach ($transactions as $transaction) {
                        AccountTransaction::where('transaction_id', $transaction->id)->forcedelete();
                        $transaction->delete();
                    }
                    if (!$contact->is_default) {
                        $contact->delete();
                    }
                    $output = [
                        'success' => true,
                        'msg' => __("contact deleted successfully")
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => __("You cannot delete this contact ")
                    ];
                }
            } catch (\Exception $e) {
                Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
                $output = [
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }
            return $output;
        }
    }
    public function getImportBalance(){
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }
        $zip_loaded = extension_loaded('zip') ? true : false;
        //Check if zip extension it loaded or not.
        if ($zip_loaded === false) {
            $output = [
                'success' => 0,
                'msg' => 'Please install/enable PHP Zip archive for import'
            ];
            return view('contact.import')
                ->with('notification', $output);
        } else {
            return view('contact.import-balance');
        }
    }

    public function exportBalance(Request $request){
        $selected_rows = explode(',', $request->input('selected_rows'));
        $business_id = $request->session()->get('user.business_id');
        $contacts = Contact::where('business_id', $business_id)
            ->whereIn('id', $selected_rows)
            ->get();

        $data = [];

        foreach($contacts as $one){
            $data[] = array("contact_id" => $one->contact_id, 'type' => $one->type, 'name' => $one->name);
        }

        // dd($data);

        $response = MatExcel::download(new ContactOpeningBalanceExport(
        $data
        ),"Contact-Opening-Balance.xls");

        ob_end_clean();
        return $response;

    }
    public function settings(){
        $business_id = request()->session()->get('user.business_id');

        $data = ContactLinkedAccount::where('contact_linked_accounts.business_id',$business_id)
                    ->join('users as u','u.id','contact_linked_accounts.created_by')
                    ->join('accounts as c','c.id','contact_linked_accounts.customer_advance')
                    ->join('accounts as s','s.id','contact_linked_accounts.supplier_advance')
                    ->leftjoin('accounts as cdr_liability','cdr_liability.id','contact_linked_accounts.customer_deposit_refund_liability_account')
                    ->leftjoin('accounts as cdr_asset','cdr_asset.id','contact_linked_accounts.customer_deposit_refund_asset_account')
                    ->select('contact_linked_accounts.*','c.name as cust','s.name as sup','u.username','cdr_liability.name as _customer_deposit_refund_liability_account','cdr_asset.name as _customer_deposit_refund_asset_account')->first();


        $liability = AccountType::getAccountTypeIdByName('Current Liabilities', $business_id)->id;


        $liability_accounts = Account::where('business_id', $business_id)->where('account_type_id', $liability)->pluck('name', 'id');



        $asset = AccountType::getAccountTypeIdByName('Current Assets', $business_id)->id;
        $asset_accounts = Account::where('business_id', $business_id)->where('account_type_id', $asset)->pluck('name', 'id');
        return view('contact.settings')
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
                DB::commit();

                return back()->with('status', $output);


        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
                'background' => 'alert-danger'
            ];
            return back()->with('status', $output);
        }
    }
    
    public function addVatNumber($id){
        
        $is_single = request()->is_single ?? false;
        
        $contact = Contact::findOrFail($id);
        
        if(empty($is_single)){
            return view('contact.update_vat_number')
                ->with(compact('contact'));
        }else{
            return view('contact.update_single_fields')
                ->with(compact('contact'));
        }
            
    }

    public function updateVatNumber(Request $request,$id){

        try {
            $input = $request->except('_token','_method');
            
            if($request->hasFile('image')){
                $imageName = Media::uploadFile($request->file('image'));
                $input['image']=$imageName;
            }
            
            Contact::where('id',$id)->update($input);
            
            $contact = Contact::findOrFail($id);
            

            $output = [
                'success' => 1,
                'msg' => __('message.success'),
                'background' => 'alert-success',
                'contact' => $contact
            ];
            DB::commit();

            return $output;


        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
                'background' => 'alert-danger'
            ];
            return $output;
        }
    }


    public function postImportBalance(Request $request)
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }
            //Set maximum php execution time
            ini_set('max_execution_time', 0);
            if ($request->hasFile('contacts_csv')) {
                $file = $request->file('contacts_csv');
                $parsed_array = Excel::toArray([], $file);
                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $formated_data = [];
                $is_valid = true;
                $error_msg = '';
                DB::beginTransaction();
                $ob_data = array();
                foreach ($imported_data as $key => $value) {

                    if(!empty($value[0])){
                        $contact_id = $value[0];

                        $contact = Contact::where('contact_id',$contact_id)->first();
                        // if(count($value) % 2 == 0){
                        //      $is_valid =  false;
                        //      $error_msg = "Number of columns mismatch";
                        //      break;
                        // }

                        for ($i = 3; $i < count($value); $i += 3) {

                           if(!empty($contact) && !empty($value[$i + 2]) && !empty($value[$i + 1]) && !empty($value[$i])){
                                $opening_balance = $value[$i + 1];
                                $invoice_no = $value[$i + 2];

                                $currentDate = new \DateTime();
                                $currentDate->sub(new \DateInterval('P' . $value[$i] . 'D'));
                                $transaction_date = $currentDate->format('Y-m-d');
                                $ob_data[] = [$contact->id,$opening_balance, $transaction_date];
                                $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $opening_balance, $transaction_date, $invoice_no);
                            }
                        }
                    }
                }


                $output = [
                    'success' => 1,
                    'msg' => __('product.file_imported_successfully'),
                    'background' => 'alert-success'
                ];
                DB::commit();

                return back()->with('notification', $output);

            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
                'background' => 'alert-danger'
            ];
            return back()->with('notification', $output);
        }

    }
}
