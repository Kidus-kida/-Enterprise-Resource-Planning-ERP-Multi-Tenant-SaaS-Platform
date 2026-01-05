<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Customer;
use Modules\Contacts\Models\Media;
use Illuminate\Http\Request;
use Modules\Contacts\Models\UserContactAccess;
use App\DataTables\ContactDataTable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Modules\Contacts\Models\NotificationTemplate;
// use Modules\Contacts\ModelsReferenceCount;
use App\Models\User;
use Carbon\Carbon;

use Modules\Contacts\Models\Vehicle;

use Modules\Contacts\Models\ContactGroup;
use Modules\Contacts\Models\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Utils\NotificationUtil;
use App\Utils\Util;
use Modules\Contacts\Models\BusinessLocation;
use Spatie\Activitylog\Models\Activity;
use App\Account;
use Modules\Contacts\Models\AccountType;
use Modules\Contacts\Models\AccountGroup;
use Modules\Contacts\Models\ContactLedger;
use Modules\Contacts\Models\AccountTransaction;
use Modules\Contacts\Models\ContactLinkedAccount;
use App\System;
use Modules\Contacts\Models\TransactionPayment;
use App\Notifications\CustomerNotification;
use Mpdf\Mpdf;



class ContactController extends Controller                   
{
    protected $moduleUtil;
    protected $commonUtil;
    protected $transactionUtil;
    protected $businessUtil;
    protected $notificationUtil;
    protected $payable_customer_txns = ['cheque_return', 'direct_customer_loan', 'customer_loan', 'property_sell', 'route_operation', 'expense', 'sell', 'fpos_sale', 'vat_price_adjustment','opening_balance','fleet_opening_balance'];
    protected $payable_supplier_txns = ['cheque_return','property_purchase','expense','opening_balance','purchase'];

  public function __construct(
    ModuleUtil $moduleUtil,
    TransactionUtil $transactionUtil,
    BusinessUtil $businessUtil,
    NotificationUtil $notificationUtil,
    Util $commonUtil
) {
    $this->moduleUtil = $moduleUtil;
    $this->transactionUtil = $transactionUtil;
    $this->businessUtil = $businessUtil;
    $this->notificationUtil = $notificationUtil;
    $this->commonUtil = $commonUtil;
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

        if (empty($business_id)) {
            $business_id = auth()->user()->business_id;
        }
        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && $type == 'customer');

        // Get contact fields from session or set to empty array
        $contact_fields = session('business.contact_fields', []);

        // Get user groups for dropdown
        $user_groups = User::forDropdown($business_id);

        // Check if it's a property customer
        $is_property = isset($is_property_customer);

        // Check customer code and get contact ID
        if (empty($business_id)) {
            $business_id = auth()->user()->business_id;
        }
        $contact_id = $this->businessUtil->check_customer_code($business_id);
return $dataTable->render('pages.contacts.index', compact('type', 'reward_enabled', 'contact_fields', 'is_property', 'user_groups','pageTitle', 'contact_id'));
    


       
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
        if (empty($business_id)) {
            $business_id = auth()->user()->business_id;
        }
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

        // Safe business ID
        $business_id = session()->get('user.business_id') ?? auth()->user()->business_id;

        if (!$business_id) {
            return back()->withInput()->with(notify(__('Business ID not found'), 'error'));
        }

        // Subscription check
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
            'name', 'email', 'mobile', 'landline', 'alternate_mobile',
            'city', 'state', 'country', 'address', 'landmark',
            'supplier_group_id', 'customer_group_id', 'nic_number'
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

        // Create a success notification
        return redirect()
            ->route('contacts.index')
            ->with(notify(__('Contact added successfully')));

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error($e);

        // Create an error notification
        return back()
            ->withInput()
            ->with(notify(__('Something went wrong: ') . $e->getMessage(), 'error'));
    }
}



    public function show($id)
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id') ?? auth()->user()->business_id;
        
        $contact = Contact::where('contacts.id', $id)
            ->where('contacts.business_id', $business_id)
            ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->select(
                DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as purchase_paid"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as invoice_received"),
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as opening_balance_paid"),
                'contacts.*'
            )->groupBy('contacts.id')->first();

        if (empty($contact)) {
            abort(404, 'Contact not found');
        }

        $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
        
        //get contact view type : ledger, notes etc.
        $view_type = request()->get('view');
        if (is_null($view_type)) {
            $view_type = 'contact_info';
        }
        
        return view('pages.contacts.show')
            ->with(compact('contact', 'business_locations', 'view_type'));
    }

   
     public function edit($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id') ?? auth()->user()->business_id;
            $business_details = $this->businessUtil->getDetails($business_id);
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
            $opening_balance = (!empty($ob_transaction) && !empty($ob_transaction->final_total)) ? $ob_transaction->final_total : 0;
            //Deduct paid amount from opening balance.
            if (!empty($opening_balance)) {
                $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                if (!empty($opening_balance_paid)) {
                    $opening_balance = $opening_balance - $opening_balance_paid;
                }
                $opening_balance = $this->commonUtil->num_f($opening_balance, false, $business_details);
            }

            if($contact->type == 'customer'){
                $notifications = NotificationTemplate::customerNotifications();
            }else{
                $notifications = NotificationTemplate::supplierNotifications();
            }
            
            $customers = Contact::customersDropdown($business_id, false);

            $user_groups = User::forDropdown($business_id);
            if (empty($business_id)) {
                $business_id = auth()->user()->business_id;
            }
            $contact_id = $this->businessUtil->check_customer_code($business_id);
            return view('pages.contacts.edit')
                ->with(compact('notifications','contact','customers', 'types', 'customer_groups', 'supplier_groups', 'opening_balance', 'ob_transaction','user_groups', 'contact_id'));
        }

        return redirect()->back();
    }


   
   public function update(Request $request, $id)
{
    if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
        abort(403, 'Unauthorized action.');
    }
    
    if (request()->ajax()) {
        try {
            $input = $request->only([
                'address_2', 'address_3', 'sub_customer', 'sub_customers', 
                'vat_number', 'address', 'credit_notification', 
                'transaction_date', 'should_notify', 'contact_id', 
                'nic_number', 'type', 'supplier_business_name', 
                'name', 'tax_number', 'pay_term_number', 
                'pay_term_type', 'mobile', 'landline', 
                'alternate_number', 'city', 'state', 'country', 
                'landmark', 'customer_group_id', 'supplier_group_id', 
                'custom_field1', 'custom_field2', 'custom_field3', 
                'custom_field4', 'email'
            ]);
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

                if ($request->hasFile('image')) {
                    $imageName = Media::uploadFile($request->file('image'));
                    $input['image'] = $imageName;
                }
                if ($request->hasFile('signature')) {
                    $signatureName = Media::uploadFile($request->file('signature'));
                    $input['signature'] = $signatureName;
                }

                // Password validation
                if (!empty(request()->password)) {
                    $validator = Validator::make(request()->all(), [
                        'password' => 'required|min:4|max:255',
                        'confirm_password' => 'required|same:password'
                    ]);
                    if ($validator->fails()) {
                        return [
                            'success' => false,
                            'msg' => 'Password does not match'
                        ];
                    }
                }

                // User creation or update
                if (empty($contact_user)) {
                    if (!$this->moduleUtil->isQuotaAvailable('customers', $business_id)) {
                        return $this->moduleUtil->quotaExpiredResponse('customers', $business_id, action('ContactController@index'));
                    }
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

            // Contact ID check
            $count = 0;
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

                // Update user access
                if ($request->assigned_to) {
                    UserContactAccess::updateOrCreate(
                        ['contact_id' => $id],
                        ['user_id' => $request->assigned_to]
                    );
                } else {
                    UserContactAccess::where('contact_id', $id)->delete();
                }

                // Opening balance handling
                $ob_transaction = Transaction::where('contact_id', $id)
                    ->where('type', 'opening_balance')
                    ->first();

                if (!empty($ob_transaction)) {
                    $amount = $this->commonUtil->num_uf($request->input('opening_balance'));
                    $ob_transaction->final_total = $amount;
                    $ob_transaction->total_before_tax = $amount;
                    $ob_transaction->transaction_date = $this->transactionUtil->uf_date($request->transaction_date);
                    $ob_transaction->save();
                    // Other related transactions
                    $payable_account_id = $this->transactionUtil->account_exist_return_id('Accounts Payable');
                    $receivealbe_account_id = $this->transactionUtil->account_exist_return_id('Accounts Receivable');
                    $account_transaction = AccountTransaction::where('transaction_id', $ob_transaction->id)
                        ->whereIn('account_id', [$payable_account_id, $receivealbe_account_id])->first();
                    
                    if (!empty($account_transaction)) {
                        $account_transaction->amount = $ob_transaction->final_total;
                        $account_transaction->save();
                    }

                    $contact_ledger_trnsaction = ContactLedger::where('transaction_id', $ob_transaction->id)->first();
                    $contact_ledger_trnsaction->amount = $ob_transaction->final_total;
                    $contact_ledger_trnsaction->save();
                    // Update payment status
                    $this->transactionUtil->updatePaymentStatus($ob_transaction->id, $ob_transaction->final_total);
                } else {
                    // Add opening balance if applicable
                    if (!empty($request->input('opening_balance'))) {
                        $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $request->input('opening_balance'));
                    }
                }

                // Update notification contacts
                $notification_parameters = json_decode($request->notification_parameters);
                $contact->notification_contacts = json_encode($notification_parameters);
                $contact->save();

                // Create a success notification
                return [
                    'success' => true,
                    'msg' => __("Contact updated successfully")
                ];
            } else {
                throw new \Exception("Error processing request: Duplicate Contact ID");
            }
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage());
            // Create an error notification
            return [
                'success' => false,
                'msg' => __("Something went wrong")
            ];
        }
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

            // Check if any transaction related to this contact exists
            $count = Transaction::where('business_id', $business_id)
                ->where('contact_id', $id)
                ->where('final_total', '>', 0)
                ->count();

            if ($count == 0) {
                $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                $transactions = Transaction::where('business_id', $business_id)
                    ->where('contact_id', $id)
                    ->get();

                foreach ($transactions as $transaction) {
                    AccountTransaction::where('transaction_id', $transaction->id)->forceDelete();
                    $transaction->delete();
                }

                if (!$contact->is_default) {
                    $contact->delete();
                }

                // Create a success notification
                return [
                    'success' => true,
                    'msg' => __("Contact deleted successfully")
                ];
            } else {
                // Create a failure notification
                return [
                    'success' => false,
                    'msg' => __("You cannot delete this contact")
                ];
            }
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage());
            // Create an error notification
            return [
                'success' => false,
                'msg' => __("Something went wrong")
            ];
        }
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

    public function get_due_bal($contact_id)
    {
        $start_date = date('Y-m-d');
        $end_date   = date('Y-m-d');
        $business_id = request()->session()->get('user.business_id');

        $contact = Contact::find($contact_id);

        $opening_balance = Transaction::where('contact_id', $contact_id)->where('type', 'opening_balance')->where('payment_status', 'due')->sum('final_total');

        if ($contact->type == 'customer') {
            $opening_amount = '';
            $opening_balance_new = DB::select("select `cl`.`amount` as opening_balance from `contact_ledgers` cl left join `transactions` t on `cl`.`transaction_id` = `t`.`id` left join `business_locations` bl on `t`.`location_id` = `bl`.`id` where `cl`.`contact_id` = " . $contact_id . " and `cl`.`type` = 'debit' and `t`.`business_id` = " . $business_id . " and `t`.`type` = 'opening_balance' and date(`cl`.`operation_date`) >= '" . $start_date . "' and date(`cl`.`operation_date`) <= '" . $end_date . "' order by `cl`.`operation_date` limit 2");

            if (count($opening_balance_new) <= 1) {
                $opening_amount =  DB::select(" select (select(0 - IFNULL(amount,0))) as opening_balance from `contact_ledgers` where contact_id = '$contact_id' order by created_at ASC limit 1");
                if (count($opening_balance_new) == 0) {
                     $opening_balance_new = DB::select(" select ( select sum(`bc_cl`.`amount`) as total_paid from `contact_ledgers` bc_cl left join `transactions` bc_t on `bc_cl`.`transaction_id` = `bc_t`.`id` left join `business_locations` bc_bl on `bc_t`.`location_id` = `bc_bl`.`id` where `bc_cl`.`contact_id` =  " . $contact_id . " and `bc_cl`.`type` = 'credit' and `bc_t`.`business_id` = " . $business_id . " and date(`bc_cl`.`operation_date`)  <= '" . $start_date . "' group by `bc_cl`.`id`, `bc_cl`.`contact_id` order by bc_cl.operation_date) as before_purchase, (select sum(`cl`.`amount`) from `contact_ledgers` cl left join `transactions` t on `cl`.`transaction_id` = `t`.`id` left join `business_locations` bl on `t`.`location_id` = `bl`.`id` where `cl`.`contact_id` = " . $contact_id . " and `cl`.`type` = 'debit' and `t`.`business_id` = " . $business_id . " and date(`cl`.`operation_date`) < '" . $start_date . "' group by `cl`.`id`, `cl`.`contact_id` order by cl.operation_date)  as before_sell, (select(IFNULL(before_sell,0) - IFNULL(before_purchase,0))) as opening_balance");
                }
            } else {
                $opening_balance_new = DB::select("select `cl`.`amount` as opening_balance from `contact_ledgers` cl left join `transactions` t on `cl`.`transaction_id` = `t`.`id` left join `business_locations` bl on `t`.`location_id` = `bl`.`id` where `cl`.`contact_id` = " . $contact_id . " and `cl`.`type` = 'debit' and `t`.`business_id` = " . $business_id . " and `t`.`type` = 'opening_balance' and date(`cl`.`operation_date`) >= '" . $start_date . "' and date(`cl`.`operation_date`) <= '" . $end_date . "' order by `cl`.`operation_date`");
            }
        }
        
        $query = ContactLedger::leftjoin('transactions', 'contact_ledgers.transaction_id', 'transactions.id')
                ->leftjoin('business_locations', 'transactions.location_id', 'business_locations.id')
                ->leftjoin('transaction_payments', 'contact_ledgers.transaction_payment_id', 'transaction_payments.id')
                ->where('contact_ledgers.contact_id', $contact_id)
                ->where('transactions.business_id', $business_id)
                ->select(
                    'contact_ledgers.*',
                    'contact_ledgers.type as acc_transaction_type',
                    'transactions.type as transaction_type',
                    'transaction_payments.amount as tp_amount',
                    'transaction_payments.transaction_id as tp_transaction_id'
                    )->groupBy('contact_ledgers.id')->orderBy('contact_ledgers.id', 'asc');

        if (!empty($start_date)  && !empty($end_date)) {
            $query->whereDate('contact_ledgers.operation_date', '>=', $start_date);
            $query->whereDate('contact_ledgers.operation_date', '<=', $end_date);
        }
        $query->orderby('contact_ledgers.operation_date');
        $ledger_transactions = $query->get();

        if ($contact->type == 'customer') {
            $total_paid = $skipped_cr = 0;
            $dateTimestamp1 = date('Y-m-d',strtotime($contact->created_at));

            foreach($ledger_transactions as $val) {
                if($val->acc_transaction_type == 'credit') {
                    $transaction_payment = null;
                    if(!empty($val->transaction_payment_id)){
                        $transaction_payment = TransactionPayment::where('id', $val->transaction_payment_id)->withTrashed()->first();
                    }
                    $amount = 0;
                    if(!empty($transaction_payment)){
                        if(empty($transaction_payment->transaction_id)){ 
                            $amount = $transaction_payment->amount; 
                        }else{
                            $amount = $val->amount; 
                        }
                    }else{
                        $amount = $val->amount;
                    }

                    if($val->transaction_type == 'opening_balance' ){
                        $dateTimestamp1 = date('Y-m-d',strtotime($val->transaction_date)) ;
                        $skipped_cr += $amount;
                        continue;
                    }
                    $total_paid = $total_paid + $amount;
                }
            }

            $opening_balance_val = count($opening_balance_new) > 0 ? (isset($opening_balance_new[0]->opening_balance) ? $opening_balance_new[0]->opening_balance : 0) : 0;
            
             // Calculate total invoice amount
             $total_invoice_query = ContactLedger::leftjoin('transactions', 'contact_ledgers.transaction_id', 'transactions.id')
                ->where('contact_ledgers.contact_id', $contact_id)
                ->where('contact_ledgers.type', 'debit')
                ->where('transactions.type', '!=', 'opening_balance')
                ->where('transactions.business_id', $business_id)
                ->sum('contact_ledgers.amount');
                
            $balance_due = $opening_balance_val + $total_invoice_query - $total_paid;

            if(!empty($start_date) && $dateTimestamp1 >= $start_date){
                $balance_due = 0; // Simplified for now
            }
            if(!empty($start_date) && $dateTimestamp1 > $start_date){
                 $balance_due -=  $skipped_cr ;
            }
            return  $balance_due;
        }
        return 0;
    }
    
    public function getAdvancePayment($contact_id)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
            $business_location_id = BusinessLocation::where('business_id', $business_id)->first()->id;
            $contact_details = Contact::where('id', $contact_id)->first();
            $payment_types = $this->transactionUtil->payment_types($business_location_id);
            unset($payment_types['credit_sale']);  

            $customer_deposit_account_id = $this->transactionUtil->account_exist_return_id('Customer Deposits');
            $advance_to_supplier_account_id = $this->transactionUtil->account_exist_return_id('Advances to Suppliers');

            if ($contact_details->type == 'customer') {
                $accounts = Account::where('business_id', $business_id)->where('id', $customer_deposit_account_id)->where('is_closed', 0)->pluck('name', 'id');
            } else {
                $accounts = Account::where('business_id', $business_id)->where('id', $advance_to_supplier_account_id)->where('is_closed', 0)->pluck('name', 'id');
            }

            $prefix_type = 'advance_payment';
            $ref_count = $this->transactionUtil->onlyGetReferenceCount($prefix_type, $business_id, false);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);

            return view('transaction_payment.customer_advance_payment')
                ->with(compact('business_locations', 'business_location_id', 'contact_details', 'payment_types', 'accounts', 'payment_ref_no', 'contact_id', 'customer_deposit_account_id'));
        }
    }

    public function postAdvancePayment(Request  $request) 
    {
        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $has_reviewed = $this->transactionUtil->hasReviewed($request->input('paid_on'));
            if(!empty($has_reviewed)){
                 return redirect()->back()->with(['status' => ['success' => 0, 'msg' => __('lang_v1.review_first')]]);
            }
            
            $business_id = request()->session()->get('user.business_id');
            $contact_id = $request->input('contact_id');
            // Assuming contact_id input is the ID directly, or use finder if needed
            // $contact = Contact::where('contact_id', $contact_id)->where('business_id', $business_id)->first();
            // In ERP code it does logic to find ID. Here we assume ID is ID.
            
            $inputs = $request->only([
                'amount', 'method', 'note', 'card_number', 'card_holder_name',
                'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                'cheque_number', 'bank_account_number','bank_name','cheque_date','post_dated_cheque','update_post_dated_cheque'
            ]);
            
             if($inputs['method'] == 'cheque'){
                    if(empty($inputs['cheque_number']) || empty($inputs['bank_name']) || empty($inputs['cheque_date'])){
                        return redirect()->back()->with('status', ['success'=>false, 'msg'=>'Bank name, Cheque Date and Cheque number are required for Cheque payments']);
                    }
                }

            $inputs['paid_on'] = !empty($inputs['paid_on']) ? Carbon::parse($inputs['paid_on'])->format('Y-m-d') : date('Y-m-d');
            $inputs['cheque_date'] = !empty($inputs['cheque_date']) ? Carbon::parse($inputs['cheque_date'])->format('Y-m-d') : date('Y-m-d');
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
            $inputs['created_by'] = auth()->user()->id;
            $inputs['payment_for'] = $contact_id;
            $inputs['business_id'] = $request->session()->get('business.id');

            $payment_type = $request->type;
            $prefix_type = $request->type;
            $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);
            $inputs['payment_ref_no'] = $payment_ref_no;
            $inputs['account_id'] = $request->account_id;
            
             //Upload documents if added
            // $inputs['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

            DB::beginTransaction();

            $transaction = $this->transactionUtil->createAdvancePaymentTransaction($business_id, $contact_id, $inputs['amount'], $inputs['account_id'], $payment_type, $inputs['paid_on'],null,$inputs);
            $inputs['transaction_id'] = $transaction->id;
            
            $contact = Contact::findOrFail($contact_id);
            $account_id = $inputs['account_id'];
            $post_dated =  $this->transactionUtil->account_exist_return_id('Post Dated Cheques');
            
            // Simplified logic for post dated cheque accounts
            
            $parent_payment = TransactionPayment::create($inputs);
            ContactLedger::where('transaction_id', $transaction->id)->update(['transaction_payment_id' => $parent_payment->id]);
            AccountTransaction::where('transaction_id', $transaction->id)->update(['transaction_payment_id' => $parent_payment->id]);
            
            $transaction->contact = $contact;
            $transaction->payment_ref_number = $payment_ref_no;
            $this->notificationUtil->autoSendNotification($business_id, 'payment_received', $transaction, $transaction->contact);

            DB::commit();
            $output = ['success' => true, 'msg' => __('purchase.payment_added_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect()->back()->with(['status' => $output]);
    }

    public function getDirectLoan($contact_id)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact_details = Contact::where('id', $contact_id)->first();
            $cash_account_id = $this->transactionUtil->account_exist_return_id('Cash');
            $accounts = Account::where('business_id', $business_id)->where('id', $cash_account_id)->where('is_closed', 0)->pluck('name', 'id');

            return view('pages.contacts.direct_loan')
                ->with(compact('contact_details', 'accounts', 'contact_id'));
        }
    }

    public function postDirectLoan(Request  $request, $contact_id) 
    {
        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $has_reviewed = $this->transactionUtil->hasReviewed($request->input('paid_on'));
            if(!empty($has_reviewed)){
                 return redirect()->back()->with(['status' => ['success' => 0, 'msg' => 'Please review first']]);
            }
            
            $business_id = request()->session()->get('user.business_id');
            $business_location_id = BusinessLocation::where('business_id', $business_id)->first()->id;
            $inputs = $request->only(['amount','paid_on']);
            $inputs['paid_on'] = !empty($inputs['paid_on']) ? Carbon::parse($inputs['paid_on'])->format('Y-m-d') : date('Y-m-d');
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
            
            $prefix_type = 'sell_payment';
            $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);
            
            $ob_data = [
                'transaction_note' => request()->note,
                'business_id' => $business_id,
                'location_id' => $business_location_id,
                'type' => 'direct_customer_loan',
                'status' => 'final',
                'payment_status' => 'due',
                'contact_id' => $contact_id,
                'transaction_date' => $inputs['paid_on'],
                'total_before_tax' => $inputs['amount'],
                'final_total' => $inputs['amount'],
                'invoice_no' => $payment_ref_no,
                'created_by' => request()->session()->get('user.id')
            ];

            DB::beginTransaction();
            
            $transaction = Transaction::create($ob_data);
            
            $cash_account_id = $this->transactionUtil->account_exist_return_id('Cash');
            $receivable_id = $this->transactionUtil->account_exist_return_id('Accounts Receivable');

            $account_transaction_data = [
                'amount' => abs($transaction->final_total),
                'account_id' => $cash_account_id,
                'contact_id' => $contact_id,
                'operation_date' => $inputs['paid_on'],
                'created_by' => $transaction->created_by,
                'transaction_id' => $transaction->id,
                'note' => request()->note,
            ];

            $account_transaction_data['type'] = 'credit';
            AccountTransaction::createAccountTransaction($account_transaction_data);

            $account_transaction_data['account_id'] = $receivable_id;
            $account_transaction_data['type'] = 'debit';
            AccountTransaction::createAccountTransaction($account_transaction_data);
            
            $transaction->contact = Contact::findOrFail($contact_id);

            DB::commit();
            
            $transaction->payment_ref_number = $payment_ref_no;
            $this->notificationUtil->autoSendNotification($transaction->business_id,'customer_loan_given' , $transaction, $transaction->contact);

            $output = ['success' => true, 'msg' => 'Success'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => false, 'msg' => 'Something went wrong'];
        }
        return redirect()->back()->with(['status' => $output]);
    }
    
    public function getRefundDeposit($contact_id)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
            $business_location_id = BusinessLocation::where('business_id', $business_id)->first()->id;
            $contact_details = Contact::where('id', $contact_id)->first();
            $payment_types = $this->transactionUtil->payment_types($business_location_id);
            unset($payment_types['credit_sale']);
            
            if($contact_details->type == 'customer'){
                unset($payment_types['cheque']);
            }else{
                unset($payment_types['bank']);
                unset($payment_types['bank_transfer']);
            }
            
            $bank_account_group_id = AccountGroup::getGroupByName('Bank Account');
            $bank_accounts = Account::where('business_id', $business_id)->where('asset_type', $bank_account_group_id->id)->get();
            
            $current_liability_account_type_id = AccountType::where('name', 'Current Liabilities')->where('business_id', $business_id)->first();
            $current_accounts = Account::where('business_id', $business_id)->where('is_closed', 0)->pluck('name', 'id');
            
            $security_deposit = Transaction::leftjoin('transaction_payments','transactions.id','transaction_payments.transaction_id')
                                            ->where('transactions.business_id',$business_id)
                                            ->where('transactions.type','security_deposit')
                                            ->where('transactions.contact_id',$contact_id)->sum('transactions.final_total');
                                            
            $security_deposit_paid = Transaction::leftjoin('transaction_payments','transactions.id','transaction_payments.transaction_id')
                                            ->where('transactions.business_id',$business_id)
                                            ->where('transactions.type','security_deposit_refund')
                                            ->where('transactions.contact_id',$contact_id)->sum('transactions.final_total');
            $balance = $security_deposit - $security_deposit_paid;
            
            $settings = ContactLinkedAccount::where('business_id',$business_id)->first();

            $prefix_type = 'advance_payment';
            $ref_count = $this->transactionUtil->onlyGetReferenceCount($prefix_type, $business_id, false);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);

            return view('pages.contacts.refund_deposit')
                ->with(compact('bank_accounts','settings','current_accounts','balance','business_locations', 'business_location_id', 'contact_details', 'payment_types',  'payment_ref_no', 'contact_id'));
        }
    }

    public function postRefundDeposit(Request  $request) 
    {
        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $has_reviewed = $this->transactionUtil->hasReviewed($request->input('paid_on'));
            if(!empty($has_reviewed)){
                 return redirect()->back()->with(['status' => ['success' => 0, 'msg' => __('lang_v1.review_first')]]);
            }
            
            $business_id = request()->session()->get('user.business_id');
            $contact_id = $request->input('contact_id');
            $contact_id = Contact::where('contact_id', $contact_id)->where('business_id', $business_id)->first()->id;

            $inputs = $request->only([
                'amount', 'method', 'note', 'card_number', 'card_holder_name',
                'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                'cheque_number', 'bank_account_number','bank_name','cheque_date','post_dated_cheque','update_post_dated_cheque'
            ]);
            
            if($inputs['method'] == 'cheque'){
                if(empty($inputs['cheque_number']) || empty($inputs['bank_name'])){
                    return redirect()->back()->with('status', ['success'=>false, 'msg'=>'Bank name and Cheque number are required for Cheque payments']);
                }else{
                    $chequesAdded = $this->transactionUtil->checkCheques($inputs['cheque_number'], $inputs['bank_name']);
                    if($chequesAdded > 0){
                        return redirect()->back()->with('status', ['success'=>false, 'msg'=>'Cheque with the same number and bank name already exists!']);
                    }
                }
            }

            $inputs['paid_on'] = !empty($inputs['paid_on']) ? Carbon::parse($inputs['paid_on'])->format('Y-m-d') : date('Y-m-d');
            $inputs['cheque_date'] = !empty($inputs['cheque_date']) ? Carbon::parse($inputs['cheque_date'])->format('Y-m-d') : date('Y-m-d');
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
            $inputs['created_by'] = auth()->user()->id;
            $inputs['payment_for'] = $contact_id;
            $inputs['business_id'] = $request->session()->get('business.id');

            if (in_array($inputs['method'], ['custom_pay_1', 'custom_pay_2', 'custom_pay_3'])) {
                 $inputs['transaction_no'] = $request->input('transaction_no_1'); 
            }

            $payment_type = 'security_deposit_refund';
            $prefix_type = $request->type;
            $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);
            $inputs['payment_ref_no'] = $payment_ref_no;
            $inputs['account_id'] = $request->account_id;
            
            DB::beginTransaction();
            $settings = ContactLinkedAccount::where('business_id',$business_id)->first();

            $transaction = $this->transactionUtil->createAdvancePaymentTransaction($business_id, $contact_id, $inputs['amount'], $inputs['account_id'], $payment_type, $inputs['paid_on'],$settings->customer_deposit_refund_liability_account,$inputs);
            $inputs['transaction_id'] = $transaction->id;
            
            $contact = Contact::findOrFail($contact_id);
            $account_id = $inputs['account_id'];
            $post_dated =  $this->transactionUtil->account_exist_return_id('Post Dated Cheques');
            $issued_post_dated =  $this->transactionUtil->account_exist_return_id('Issued Post Dated Cheques');
            
            if ($payment_type == 'security_deposit_refund') {
                if ($contact->type == 'customer') {
                    if(!empty($inputs) && !empty($inputs['update_post_dated_cheque'])){
                        $inputs['related_account_id'] = $account_id;
                        $inputs['account_id'] = $issued_post_dated;
                    }
                }
                if ($contact->type == 'supplier') {
                    if(!empty($inputs) && !empty($inputs['update_post_dated_cheque'])){
                        $inputs['related_account_id'] = $account_id;
                        $inputs['account_id'] = $post_dated;
                    }
                }
            }

            $parent_payment = TransactionPayment::create($inputs);
            AccountTransaction::where('transaction_id', $transaction->id)->update(['transaction_payment_id' => $parent_payment->id]);
            
            $transaction->contact = Contact::where('id', $contact_id)->first();
            $transaction->payment_ref_number = $payment_ref_no;
            $this->notificationUtil->autoSendNotification($business_id, 'payment_received', $transaction, $transaction->contact);

            DB::commit();
            $output = ['success' => true, 'msg' => __('lang_v1.security_deposit_added_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect()->back()->with(['status' => $output]);
    }

    public function getRefundPayment($contact_id)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact_details = Contact::where('id', $contact_id)->first();
            $payment_types = ['cash' => __('lang_v1.cash'), 'cheque' => __('lang_v1.cheque'), 'bank_transfer' => __('lang_v1.bank')];

            $bank_account_group_id = AccountGroup::getGroupByName('Bank Account');
            $bank_accounts = Account::where('business_id', $business_id)->where('asset_type', $bank_account_group_id->id)->get();
            $invoices = Transaction::where('contact_id', $contact_id)->where('type', 'sell')->pluck('invoice_no', 'invoice_no');
            
            $advance_to_supplier_account_id = $this->transactionUtil->account_exist_return_id('Advances to Suppliers');
            $customer_deposit_account_id = $this->transactionUtil->account_exist_return_id('Customer Deposits');

            if ($contact_details->type == 'customer') {
                $accounts = Account::where('business_id', $business_id)->where('id', $customer_deposit_account_id)->where('is_closed', 0)->pluck('name', 'id');
            } else {
                $accounts = Account::where('business_id', $business_id)->where('id', $advance_to_supplier_account_id)->where('is_closed', 0)->pluck('name', 'id');
            }

            $cheque_array = [];
            $cheque_banks = AccountTransaction::leftjoin('accounts', 'account_transactions.account_id', 'accounts.id')
                ->leftjoin('transaction_payments', 'account_transactions.transaction_payment_id', 'transaction_payments.parent_id')
                ->leftjoin('transactions', 'transaction_payments.transaction_id', 'transactions.id')
                ->where('account_transactions.sub_type', 'deposit')
                ->where('transaction_payments.method', 'cheque')
                ->where('account_transactions.type', 'debit')
                ->where('transactions.contact_id', $contact_id)
                ->pluck('accounts.name', 'accounts.id');

            return view('pages.contacts.refund_payment')
                ->with(compact('invoices', 'bank_accounts', 'contact_details', 'payment_types', 'accounts', 'contact_id', 'customer_deposit_account_id', 'cheque_array', 'cheque_banks'));
        }
    }

    public function postRefundPayment(Request  $request)
    {
        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $has_reviewed = $this->transactionUtil->hasReviewed($request->input('paid_on'));
            if(!empty($has_reviewed)){
                 return redirect()->back()->with(['status' => ['success' => 0, 'msg' => __('lang_v1.review_first')]]);
            }
            
            $business_id = request()->session()->get('user.business_id');
            $contact_id = $request->input('contact_id');
            $contact_id = Contact::where('contact_id', $contact_id)->where('business_id', $business_id)->first()->id;

            $inputs = $request->only([
                'amount', 'method', 'note', 'card_number', 'card_holder_name', 'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security', 'cheque_date',
                'cheque_number', 'bank_account_number', 'transfer_date', 'bank_name', 'sale_invoice_bill_number', 'cheque_return_charges','post_dated_cheque'
            ]);

            $inputs['paid_on'] = !empty($inputs['paid_on']) ? Carbon::parse($inputs['paid_on'])->format('Y-m-d') : date('Y-m-d');
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
            $inputs['created_by'] = auth()->user()->id;
            $inputs['payment_for'] = $contact_id;
            $inputs['business_id'] = $request->session()->get('business.id');
            $method =  $request->input('method');

            if ($request->type == 'cheque_return') {
                $method = 'bank_transfer';
                $inputs['method'] =  $method;
                $inputs['cheque_number'] =  $this->getPaymentDetailsById($request->cheque_number_return)->cheque_number;
                $inputs['bank_name'] =  $this->getBankNameByBankId($request->cheque_bank);
            }

            if (in_array($inputs['method'], ['custom_pay_1', 'custom_pay_2', 'custom_pay_3'])) {
                $inputs['transaction_no'] = $request->input('transaction_no_1'); 
            }

            $payment_type = $request->type;
            $prefix_type = $request->type;
            $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);
            $inputs['payment_ref_no'] = $payment_ref_no;
            $cheque_bank = $request->cheque_bank;
            $inputs['account_id'] = $cheque_bank;
            $inputs['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

            DB::beginTransaction();

            $transaction = $this->transactionUtil->createRefundPaymentTransaction($business_id, $contact_id, $inputs['amount'], $inputs['account_id'], $payment_type, $inputs['paid_on'],$inputs['cheque_number']);
            $inputs['transaction_id'] = $transaction->id;

            unset($inputs['sale_invoice_bill_number']);
            unset($inputs['cheque_return_charges']);
            
            $inputs['transfer_date'] = !empty($inputs['transfer_date']) ? Carbon::parse($inputs['transfer_date'])->format('Y-m-d') : null;
            $inputs['cheque_date'] = !empty($inputs['cheque_date']) ? Carbon::parse($inputs['cheque_date'])->format('Y-m-d') : null;
            $inputs['is_return'] = 1;

            DB::commit();
            
            if ($request->type == 'cheque_return' && !empty($transaction)) {
                $transaction->bank_name = Account::find($cheque_bank)->name ?? '';
                $transaction->contact = Contact::where('id', $transaction->contact_id)->first();
                $transaction->payment_ref_number = $payment_ref_no;
                $this->notificationUtil->autoSendNotification($business_id, 'cheque_return', $transaction, $transaction->contact);
            }

            $output = ['success' => true, 'msg' => __('purchase.payment_added_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect()->back()->with(['status' => $output]);
    }
    
    public function getBankNameByBankId($bank_id){
        if($bank_id){
            $bank = Account::find($bank_id);
            if($bank){
                return $bank->name;
            }else{
                return null;
            }
        }
    }
    
    public function getPaymentDetailsById($payment_id)
    {
        $payment = TransactionPayment::find($payment_id);
        $business_id = request()->session()->get('business.id');
        if ($payment->method == 'cheque') {
            $amount = TransactionPayment::where('business_id', $business_id)->whereNotNull('transaction_id')->where('cheque_number', $payment->cheque_number)->sum('amount');
        }
        $payment->amount = $amount ?? $payment->amount; // Added fallback
        return $payment;
    }
    
    public function getChequeDropdownByBankId($bank_id, $contact_id)
    {
        $business_id = request()->session()->get('business.id');
        $cheque_banks = AccountTransaction::leftjoin('accounts', 'account_transactions.account_id', 'accounts.id')
            ->leftjoin('transaction_payments', function ($join) use ($business_id) {
                $join->on('account_transactions.transaction_payment_id', 'transaction_payments.id');
            })
            ->leftjoin('transactions', 'transaction_payments.transaction_id', 'transactions.id')
            ->where('account_transactions.sub_type', 'deposit')
            ->where('account_transactions.type', 'debit')
            ->whereNotNull('transfer_transaction_id')
            ->where('transactions.contact_id', $contact_id)
            ->where('accounts.id', $bank_id)
            ->groupBy('account_transactions.id')
            ->select('transaction_payments.cheque_number', 'transaction_payments.parent_id', 'transaction_payments.id')->get();

        $array = [];
        foreach ($cheque_banks as $cheque_bank) {
            if (!empty($cheque_bank->parent_id)) {
                $array[$cheque_bank->parent_id] = $cheque_bank->cheque_number;
            } else {
                $array[$cheque_bank->id] = $cheque_bank->cheque_number;
            }
        }

        $cheque_banks2 = AccountTransaction::leftjoin('accounts', 'account_transactions.account_id', 'accounts.id')
            ->leftjoin('transaction_payments', function ($join) use ($business_id) {
                $join->on('account_transactions.transaction_payment_id', 'transaction_payments.parent_id');
            })
            ->leftjoin('transactions', 'transaction_payments.transaction_id', 'transactions.id')
            ->where('account_transactions.sub_type', 'deposit')
            ->where('account_transactions.type', 'debit')
            ->whereNotNull('transfer_transaction_id')
            ->where('transactions.contact_id', $contact_id)
            ->where('accounts.id', $bank_id)
            ->groupBy('account_transactions.id')
            ->select('transaction_payments.cheque_number', 'transaction_payments.parent_id', 'transaction_payments.id')->get();

        foreach ($cheque_banks2 as $cheque_bank2) {
            if (!empty($cheque_bank2->parent_id)) {
                $array[$cheque_bank2->parent_id] = $cheque_bank2->cheque_number;
            } else {
                $array[$cheque_bank2->id] = $cheque_bank2->cheque_number;
            }
        }
        $html = $this->transactionUtil->createDropdownHtml($array, 'Please Select');
        return $html;
    }

    public function getSecurityDeposit($contact_id)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
            $business_location_id = BusinessLocation::where('business_id', $business_id)->first()->id;
            $contact_details = Contact::where('id', $contact_id)->first(); // Fixed query to match ID directly
            $payment_types = $this->transactionUtil->payment_types($business_location_id);
            unset($payment_types['credit_sale']);

            $clati = 0;
            $current_liability_account_type_id = AccountType::where('name', 'Current Liabilities')->where('business_id', $business_id)->first();
            if (!empty($current_liability_account_type_id)) {
                $clati = $current_liability_account_type_id->id;
            }

            $current_libility_account_id = $this->transactionUtil->account_exist_return_id('Customer Deposits');
            $current_libility_accounts = Account::where('business_id', $business_id)->where('parent_account_id',  $current_libility_account_id)->where('is_closed', 0)->pluck('name', 'id');

            if (count($current_libility_accounts) == 0) {
                $current_libility_accounts = Account::where('id', $current_libility_account_id)->where('is_closed', 0)->pluck('name', 'id');
            }

            $account_access = $this->moduleUtil->hasThePermissionInSubscription($business_id, 'access_account');
            $disabled = '';
            $message = '';

            if (!$account_access) {
                // Simplified system property access for now
                $font_size = '12px'; // Default or fetch from System::getProperty if needed
                $color = 'red';
                $msg = 'Access denied or configured via system settings.';
                // ... logic for message ...
                 $disabled = 'disabled';
                 //$current_libility_account_id = 0;
            }

            $prefix_type = 'security_deposit';
            $ref_count = $this->transactionUtil->onlyGetReferenceCount($prefix_type, $business_id, false);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);

            $customer_deposit_account_id = $this->transactionUtil->account_exist_return_id('Cash');
            $settings = ContactLinkedAccount::where('business_id',$business_id)->first();
            
            if($contact_details->type == 'supplier'){
                if(!empty($settings)){
                    $customer_deposit_account_id = $settings->supplier_advance;
                }
            }else{
                if(!empty($settings)){
                    $customer_deposit_account_id = $settings->customer_advance;
                }
            }
            
            $accounts = Account::where('business_id', $business_id)->where('id', $customer_deposit_account_id)->where('is_closed', 0)->pluck('name', 'id');
            $security_deposit_already = Transaction::where('contact_id', $contact_id)->where('type', 'security_deposit')->first();

            return view('pages.contacts.security_deposit')
                ->with(compact(
                    'business_locations',
                    'business_location_id',
                    'security_deposit_already',
                    'contact_details',
                    'payment_types',
                    'accounts',
                    'payment_ref_no',
                    'contact_id',
                    'customer_deposit_account_id',
                    'current_libility_accounts',
                    'current_libility_account_id',
                    'disabled',
                    'message'
                ));
        }
    }

    public function postSecurityDeposit(Request $request)
    {
        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $has_reviewed = $this->transactionUtil->hasReviewed($request->input('paid_on'));
            if(!empty($has_reviewed)){
                 return redirect()->back()->with(['status' => ['success' => 0, 'msg' => __('lang_v1.review_first')]]);
            }
            
            $business_id = request()->session()->get('user.business_id');
            $contact_id = $request->input('contact_id');
            $contact_id = Contact::where('contact_id', $contact_id)->where('business_id', $business_id)->first()->id;

            $inputs = $request->only([
                'amount', 'method', 'note', 'card_number', 'card_holder_name',
                'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                'cheque_number', 'bank_account_number','bank_name','cheque_date','post_dated_cheque','update_post_dated_cheque'
            ]);
            
            if($inputs['method'] == 'cheque'){
                if(empty($inputs['cheque_number']) || empty($inputs['bank_name'])){
                    return redirect()->back()->with('status', ['success'=>false, 'msg'=>'Bank name and Cheque number are required for Cheque payments']);
                }else{
                    $chequesAdded = $this->transactionUtil->checkCheques($inputs['cheque_number'], $inputs['bank_name']);
                    if($chequesAdded > 0){
                         return redirect()->back()->with('status', ['success'=>false, 'msg'=>'Cheque with the same number and bank name already exists!']);
                    }
                }
            }

            $inputs['paid_on'] = !empty($inputs['paid_on']) ? Carbon::parse($inputs['paid_on'])->format('Y-m-d') : date('Y-m-d');
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
            $inputs['created_by'] = auth()->user()->id;
            $inputs['payment_for'] = $contact_id;
            $inputs['business_id'] = $request->session()->get('business.id');

            if (in_array($inputs['method'], ['custom_pay_1', 'custom_pay_2', 'custom_pay_3'])) {
                 $inputs['transaction_no'] = $request->input('transaction_no_1'); 
            }

            $payment_type = $request->type;
            $inputs['payment_ref_no'] = $request->payment_ref_no;
            $payment_ref_no = $inputs['payment_ref_no'];
            $business_location = BusinessLocation::where('business_id', $business_id)->first();
            $inputs['account_id'] = $request->account_id;
            $inputs['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

            DB::beginTransaction();
            $current_liability_account = $request->current_liability_account;

            $transaction = $this->transactionUtil->createAdvancePaymentTransaction($business_id, $contact_id, $inputs['amount'], $inputs['account_id'], $payment_type, $inputs['paid_on'], $current_liability_account,$inputs);
            $inputs['transaction_id'] = $transaction->id;
            
            $contact = Contact::findOrFail($contact_id);
            $account_id = $inputs['account_id'];
            $post_dated =  $this->transactionUtil->account_exist_return_id('Post Dated Cheques');
            $issued_post_dated =  $this->transactionUtil->account_exist_return_id('Issued Post Dated Cheques');
            
            if ($payment_type == 'advance_payment' || $payment_type == 'security_deposit' || $payment_type == 'security_deposit_refund') {
                 if ($contact->type == 'customer') {
                    if(!empty($inputs) && !empty($inputs['update_post_dated_cheque'])){
                        $inputs['related_account_id'] = $account_id;
                        $inputs['account_id'] = $post_dated;
                    }
                }
                if ($contact->type == 'supplier') {
                    if(!empty($inputs) && !empty($inputs['update_post_dated_cheque'])){
                        $inputs['related_account_id'] = $account_id;
                        $inputs['account_id'] = $issued_post_dated;
                    }
                }
            }

            $parent_payment = TransactionPayment::create($inputs);
            AccountTransaction::where('transaction_id', '=', $transaction->id)->update(['transaction_payment_id' => $parent_payment['id']]);
            
            $transaction->contact = Contact::where('id', $contact_id)->first();
            $transaction->payment_ref_number = $payment_ref_no;
            $this->notificationUtil->autoSendNotification($business_id, 'payment_received', $transaction, $transaction->contact);

            DB::commit();
            $output = ['success' => true, 'msg' => __('lang_v1.security_deposit_added_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }
        return redirect()->back()->with(['status' => $output]);
    }

    public function getPayContactDue($contact_id)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $due_payment_type = request()->input('type');

            $query = Contact::where('contacts.id', $contact_id)
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id');

            if ($due_payment_type == 'purchase') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as total_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id',
                    't.transaction_date'
                );
            } elseif ($due_payment_type == 'purchase_return') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at  IS NULL), 0)) as total_return_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id'
                );
            } elseif ($due_payment_type == 'sell') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at  IS NULL), 0)) as total_paid"),
                    DB::raw("SUM(IF(t.type = 'cheque_return' AND t.status = 'final', final_total, 0)) as total_cheque_return"),
                    DB::raw("SUM(IF(t.type = 'cheque_return' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at  IS NULL  AND is_return=0), 0)) as total_paid_cheque_return"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id'
                );
            } elseif ($due_payment_type == 'sell_return') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at  IS NULL), 0)) as total_return_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id'
                );
            }

            $query->addSelect(
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at  IS NULL), 0)) as opening_balance_paid")
            );

            $contact_details = $query->first();
            $payment_line = new TransactionPayment();
            $amount_formated = $this->transactionUtil->num_f(strval($this->get_due_bal($contact_id)));

            if ($due_payment_type == 'purchase') {
                $contact_details->total_purchase = empty($contact_details->total_purchase) ? 0 : $contact_details->total_purchase;
                $payment_line->amount = $this->get_due_bal($contact_id);
                $prefix_type = 'purchase_payment';
            } elseif ($due_payment_type == 'purchase_return') {
                $payment_line->amount = $contact_details->total_purchase_return - $contact_details->total_return_paid;
                $amount_formated = $this->transactionUtil->num_f($payment_line->amount);
                $prefix_type = 'purchase_payment';
            } elseif ($due_payment_type == 'sell') {
                $contact_details->total_invoice = empty($contact_details->total_invoice) ? 0 : $contact_details->total_invoice;
                $contact_details->total_cheque_return = empty($contact_details->total_cheque_return) ? 0 : $contact_details->total_cheque_return;
                $payment_line->amount = $this->get_due_bal($contact_id);
                $prefix_type = 'sell_payment';
            } elseif ($due_payment_type == 'sell_return') {
                $payment_line->amount = $contact_details->total_sell_return - $contact_details->total_return_paid;
                $amount_formated = $this->transactionUtil->num_f($payment_line->amount);
                $prefix_type = 'sell_payment';
            }

            $contact_details->opening_balance = !empty($contact_details->opening_balance) ? $contact_details->opening_balance : 0;
            $contact_details->opening_balance_paid = !empty($contact_details->opening_balance_paid) ? $contact_details->opening_balance_paid : 0;
            $ob_due = $contact_details->opening_balance - $contact_details->opening_balance_paid;

            $business_locations = BusinessLocation::where('business_id', $business_id)->pluck('name', 'id');
            $business_location_id = BusinessLocation::where('business_id', $business_id)->first()->id;
            $contact_details->total_paid = empty($contact_details->total_paid) ? 0 : $contact_details->total_paid;
            $payment_line->method = 'cash';
            $payment_line->paid_on = Carbon::now()->toDateTimeString();

            if ($due_payment_type == 'purchase') {
                $payment_types = $this->transactionUtil->payment_types(null, false, false, false, false, true, "is_purchase_enabled");
                unset($payment_types['credit_purchase']);
            } else {
                $payment_types = $this->transactionUtil->payment_types($business_location_id);
                unset($payment_types['credit_sale']);
            }

            $accounts = $this->moduleUtil->accountsDropdown($business_id, true)->toArray();
            $ref_count = $this->transactionUtil->onlyGetReferenceCount($prefix_type, $business_id, false);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);
            $payment_line->amount = $this->get_due_bal($contact_id);

            return view('pages.contacts.pay_contact_due_modal')
                ->with(compact('business_locations', 'business_location_id', 'contact_details', 'payment_types', 'payment_line', 'due_payment_type', 'ob_due', 'amount_formated', 'accounts', 'payment_ref_no'));
        }
    }

    public function postPayContactDue(Request $request)
    {
        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('business.id');
        try {
            $contact_id = $request->input('contact_id');
            $has_reviewed = $this->transactionUtil->hasReviewed($request->input('paid_on'));
            if(!empty($has_reviewed)){
                 return redirect()->back()->with(['status' => ['success' => 0, 'msg' => __('lang_v1.review_first')]]);
            }
            
            $inputs = $request->only([
                'amount', 'method', 'note', 'card_number', 'card_holder_name',
                'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                'cheque_number', 'bank_account_number','bank_name','post_dated_cheque','update_post_dated_cheque'
            ]);
            
            if($inputs['method'] == 'cheque'){
                if(empty($inputs['cheque_number']) || empty($inputs['bank_name'])){
                    return redirect()->back()->with('status', ['success' => false, 'msg' => 'Bank name and Cheque number are required for Cheque payments']);
                }else{
                    $chequesAdded = $this->transactionUtil->checkCheques($inputs['cheque_number'], $inputs['bank_name']);
                    if($chequesAdded > 0){
                        return redirect()->back()->with('status', ['success' => false, 'msg' => 'Cheque with the same number and bank name already exists!']);
                    }
                }
            }

            $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
            $inputs['created_by'] = auth()->user()->id;
            $inputs['payment_for'] = $contact_id;
            $inputs['business_id'] = $request->session()->get('business.id');
            $inputs['cheque_date'] = !empty($request->cheque_date) ? Carbon::parse($request->cheque_date)->format('Y-m-d') : null;

            if (in_array($inputs['method'], ['custom_pay_1', 'custom_pay_2', 'custom_pay_3'])) {
                 $inputs['transaction_no'] = $request->input('transaction_no_1'); 
            }

            $due_payment_type = $request->input('due_payment_type');
            $prefix_type = 'purchase_payment';
            if (in_array($due_payment_type, ['sell', 'sell_return'])) {
                $prefix_type = 'sell_payment';
            }
            $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
            $payment_ref_no = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);
            $inputs['payment_ref_no'] = $payment_ref_no;

            if (!empty($request->input('account_id'))) {
                $inputs['account_id'] = $request->input('account_id');
            }
            
            $inputs['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            $inputs['paid_in_type'] = 'customer_page';

            DB::beginTransaction();
            
            $contact = Contact::findOrFail($contact_id);
            $post_dated =  $this->transactionUtil->account_exist_return_id('Post Dated Cheques');
            $issued_post_dated =  $this->transactionUtil->account_exist_return_id('Issued Post Dated Cheques');
            
            if(!empty($inputs['update_post_dated_cheque'])){
                $inputs['related_account_id'] = $request->input('account_id');
                if($contact->type ==  'supplier'){
                    $inputs['account_id'] = ($due_payment_type == 'purchase_return') ? $post_dated : $issued_post_dated;
                }
                if ($contact->type ==  'customer') {
                    $inputs['account_id'] = ($due_payment_type == 'sell_return') ? $issued_post_dated : $post_dated;
                }
            }

            $parent_payment = TransactionPayment::create($inputs);
            $inputs['transaction_type'] = $due_payment_type;

            $account_payable = Account::where('business_id', $business_id)->where('name', 'Accounts Payable')->where('is_closed', 0)->first();
            $account_payable_id = !empty($account_payable) ? $account_payable->id : 0;

            $account_transaction_data = [
                'contact_id' => $contact_id,
                'amount' => $parent_payment->amount,
                'account_id' => $parent_payment->account_id,
                'type' => 'credit',
                'operation_date' => $parent_payment->paid_on,
                'created_by' => auth()->user()->id,
                'transaction_payment_id' => $parent_payment->id,
                'note' => null,
                'post_dated_cheque' => $request->post_dated_cheque,
                'update_post_dated_cheque' => $request->update_post_dated_cheque
            ];

            $account_transaction_data['account_id'] = $request->account_id;

            if ($contact->type ==  'supplier') {
                if ($due_payment_type == 'purchase_return') {
                    $purchase_return_due = Transaction::where('contact_id', $contact_id)->whereIn('type', ['purchase_return'])->whereIn('payment_status', ['due', 'partial'])->first();
                    $transaction = $purchase_return_due; 
                    
                    if(!empty($inputs['update_post_dated_cheque'])){
                        $account_transaction_data['related_account_id'] = $request->input('account_id');
                        $account_transaction_data['account_id'] = $post_dated;
                    }
                    $account_transaction_data['type'] = 'debit';
                    $account_transaction_data['sub_type'] = 'payment';
                    AccountTransaction::createAccountTransaction($account_transaction_data);

                    $account_transaction_data['account_id'] =  $account_payable_id;
                    $account_transaction_data['type'] = 'credit';
                    AccountTransaction::createAccountTransaction($account_transaction_data);

                    $account_transaction_data['type'] = 'credit';
                    ContactLedger::createContactLedger($account_transaction_data);
                } else {
                    $due_transaction_id = Transaction::where('contact_id', $contact_id)->whereIn('type', $this->payable_supplier_txns)->whereIn('payment_status', ['due', 'partial'])->first();
                    $transaction = $due_transaction_id;
                    
                    if ($inputs['method'] == 'bank_transfer' || $inputs['method'] == 'direct_bank_deposit') {
                        $account_transaction_data['account_id'] = $inputs['account_id'];
                    }

                    $account_transaction_data['type'] = 'credit';
                    if(!empty($inputs['update_post_dated_cheque'])){
                        $account_transaction_data['related_account_id'] = $request->input('account_id');
                        $account_transaction_data['account_id'] = $issued_post_dated;
                    }
                    AccountTransaction::createAccountTransaction($account_transaction_data);

                    $account_transaction_data['account_id'] = $account_payable_id;
                    $account_transaction_data['type'] = 'debit';
                    AccountTransaction::createAccountTransaction($account_transaction_data);

                    $account_transaction_data['sub_type'] = 'payment';
                    ContactLedger::createContactLedger($account_transaction_data);
                }
            }

            if ($contact->type ==  'customer') {
                if ($due_payment_type == 'sell_return') {
                    $sell_return_due = Transaction::where('contact_id', $contact_id)->whereIn('type', ['sell_return'])->whereIn('payment_status', ['due', 'partial'])->first();
                    $transaction = $sell_return_due;
                    $account_transaction_data['type'] = 'debit';
                    AccountTransaction::createAccountTransaction($account_transaction_data);
                    $account_transaction_data['sub_type'] = 'payment';
                    ContactLedger::createContactLedger($account_transaction_data);
                } else {
                    $due_transaction_id = Transaction::where('contact_id', $contact_id)->whereIn('type', $this->payable_customer_txns)->whereIn('payment_status', ['due', 'partial'])->first();
                    $transaction = $due_transaction_id;
                    $account_transaction_data['type'] = 'debit';
                    if(!empty($inputs['update_post_dated_cheque'])){
                        $account_transaction_data['related_account_id'] = $request->input('account_id');
                        $account_transaction_data['account_id'] = $post_dated;
                    }
                    AccountTransaction::createAccountTransaction($account_transaction_data);

                    $account_receivable = Account::where('business_id', $business_id)->where('name', 'Accounts Receivable')->where('is_closed', 0)->first();
                    $account_receivable_id = !empty($account_receivable) ? $account_receivable->id : 0;
                    $account_transaction_data['account_id'] = $account_receivable_id;
                    $account_transaction_data['type'] = 'credit';
                    $account_transaction_data['sub_type'] = 'ledger_show';
                    AccountTransaction::createAccountTransaction($account_transaction_data);

                    $account_transaction_data['contact_id'] = $contact_id;
                    $account_transaction_data['sub_type'] = 'payment';
                    ContactLedger::createContactLedger($account_transaction_data);
                }
            }

            $this->transactionUtil->payAtOnce($parent_payment, $due_payment_type);
            DB::commit();
            
            if(!empty($transaction)){
                $transaction->contact = $contact;
                $transaction->transaction_date = $inputs['paid_on'];
                $transaction->single_payment_amount = $inputs['amount'];
                $transaction->payment_ref_number = $payment_ref_no;
                $this->notificationUtil->autoSendNotification($business_id, 'payment_received', $transaction, $transaction->contact, true);
            }

            $output = ['success' => true, 'msg' => __('purchase.payment_added_success')];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
        }
        return redirect()->back()->with(['status' => $output]);
    }

    public function balanceDetails($id)
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id') ?? auth()->user()->business_id;
        $contact = Contact::findOrFail($id);
        
        $balance_details = ['opening_balance' => 0, 'total_purchase' => 0, 'total_sale' => 0, 'total_paid' => 0, 'total_balance' => 0];
        
        if($contact->type == 'supplier' || $contact->type == 'both'){
            $txns = Transaction::where('business_id', $business_id)
                        ->whereIn('type', ['purchase', 'opening_balance', 'purchase_return', 'expense', 'cheque_return'])
                        ->whereNull('deleted_at')
                        ->whereIn('status', ['final', 'received'])
                        ->where('contact_id', $id)
                        ->select([
                            DB::raw("SUM(IF(type = 'purchase', final_total, 0)) as purchase"),
                            DB::raw("SUM(IF(type = 'opening_balance', final_total, 0)) as opening_balance"),
                            DB::raw("SUM(IF(type = 'purchase_return', final_total, 0)) as purchase_return"),
                            DB::raw("SUM(IF(type = 'expense', final_total, 0)) as expense"),
                            DB::raw("SUM(IF(type = 'cheque_return', final_total, 0)) as cheque_return"),
                        ])
                        ->first();
            
            $pmts = TransactionPayment::where('business_id', $business_id)
                        ->whereNull('deleted_at')
                        ->whereNull('parent_id')
                        ->where('payment_for', $id)
                        ->sum('amount');
            
            if(!empty($txns)){
                $balance_details['opening_balance'] = $txns->opening_balance;
                $balance_details['total_purchase'] = ($txns->purchase + $txns->expense + $txns->cheque_return) - $txns->purchase_return;
            }
            $balance_details['total_paid'] = $pmts;
            $balance_details['total_balance'] = $balance_details['total_purchase'] + $balance_details['opening_balance'] - $balance_details['total_paid'];
        } elseif($contact->type == 'customer'){
            $txns = Transaction::where('business_id', $business_id)
                        ->whereIn('type', ['sell', 'opening_balance', 'sell_return', 'expense', 'cheque_return'])
                        ->whereNull('deleted_at')
                        ->where('status', 'final')
                        ->where('contact_id', $id)
                        ->select([
                            DB::raw("SUM(IF(type = 'sell', final_total, 0)) as sell"),
                            DB::raw("SUM(IF(type = 'opening_balance', final_total, 0)) as opening_balance"),
                            DB::raw("SUM(IF(type = 'sell_return', final_total, 0)) as sell_return"),
                            DB::raw("SUM(IF(type = 'expense', final_total, 0)) as expense"),
                            DB::raw("SUM(IF(type = 'cheque_return', final_total, 0)) as cheque_return"),
                        ])
                        ->first();
            
            $pmts = TransactionPayment::where('business_id', $business_id)
                        ->whereNull('deleted_at')
                        ->whereNull('parent_id')
                        ->where('payment_for', $id)
                        ->sum('amount');
            
            if(!empty($txns)){
                $balance_details['opening_balance'] = $txns->opening_balance;
                $balance_details['total_sale'] = ($txns->sell + $txns->expense + $txns->cheque_return) - $txns->sell_return;
            }
            $balance_details['total_paid'] = $pmts;
            $balance_details['total_balance'] = $balance_details['total_sale'] + $balance_details['opening_balance'] - $balance_details['total_paid'];
        }
        
        return view('pages.contacts.balance_details')
            ->with(compact('balance_details', 'contact'));
    }

    public function toggleActivate($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }
        
        $contact = Contact::findOrFail($id);
        $contact->active = !$contact->active;
        $contact->save();
        
        $msg = $contact->active ? __('contact activate success') : __('contact deactivate success');
        
    
         return redirect()
            ->back()
            ->with(notify(__($msg)));
    }

    public function getLedger()
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $contact_id = request()->input('contact_id');
        $contact = Contact::findOrFail($contact_id);

        $query = ContactLedger::where('contact_id', $contact_id)
            ->with(['transaction', 'transaction_payment']);

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $query->whereDate('operation_date', '>=', request()->start_date)
                  ->whereDate('operation_date', '<=', request()->end_date);
        }

        if (!empty(request()->transaction_type)) {
            $query->where('type', request()->transaction_type);
        }

        $ledger_transactions = $query->orderBy('operation_date', 'asc')->get();

        return view('pages.contacts.partials.ledger_table')
            ->with(compact('contact', 'ledger_transactions'));
    }

    public function getPayment()
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $contact_id = request()->input('contact_id');
        $contact = Contact::findOrFail($contact_id);

        $query = TransactionPayment::where('payment_for', $contact_id)
            ->whereNull('parent_id')
            ->with(['transaction']);

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $query->whereDate('paid_on', '>=', request()->start_date)
                  ->whereDate('paid_on', '<=', request()->end_date);
        }

        $payments = $query->orderBy('paid_on', 'desc')->get();

        return view('pages.contacts.partials.payments_table')
            ->with(compact('contact', 'payments'));
    }
}
