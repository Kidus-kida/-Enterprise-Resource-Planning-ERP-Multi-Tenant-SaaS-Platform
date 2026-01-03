<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\ContactGroup;
use Modules\Contacts\Models\Transaction;
use Modules\Contacts\Models\TransactionPayment; // Might need to be created if missing
use App\Models\BusinessLocation;
use App\Business;
use Modules\Accounting\Models\Account; // Or App\Models\Account
use Modules\Accounting\Models\AccountType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;

class ContactReportController extends Controller
{
    protected $transactionUtil;
    protected $productUtil;

    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    public function getOutstandingReceivedReport()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $payment_types = $this->transactionUtil->payment_types();
        // $customer_group = ContactGroup::forDropdown($business_id, false, true); // Logic might differ if ContactGroup model specific
        $customer_group = ContactGroup::where('business_id', $business_id)->pluck('name', 'id'); // Simplified
        $types = Contact::typeDropdown(true);
        // $bill_nos = Transaction::invoiveNumberDropDown('sell'); // Need this method in Transaction model?
        // $payment_ref_nos = Transaction::paymentRefNumberDropDown('sell');
        // $cheque_numbers = Transaction::chequeNumberDropDown('sell');
        
        // Use placeholders if methods missing in migrated Transaction model
        $bill_nos = []; 
        $payment_ref_nos = [];
        $cheque_numbers = [];

        return view('contacts::contact.outstanding_received_report')->with(compact(
            'suppliers',
            'business_locations',
            'customers',
            'customer_group',
            'types',
            'payment_types',
            'bill_nos',
            'payment_ref_nos',
            'cheque_numbers'
        ));
    }

    public function getIssuedPaymentDetails()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $payment_types = $this->transactionUtil->payment_types();
        $customer_group = ContactGroup::where('business_id', $business_id)->pluck('name', 'id');
        $types = Contact::typeDropdown(true);
        $bill_nos = [];
        $payment_ref_nos = [];
        $cheque_numbers = [];
        $business_details = Business::find($business_id);

        if (request()->ajax()) {
            $purchase = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'purchase')
                ->where('contacts.type', 'supplier')
                ->whereIn('transactions.payment_status', ['paid', 'partial'])
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.payment_status',
                    'transactions.final_total',
                    'tp.paid_on',
                    'tp.method',
                    'tp.cheque_number',
                    'tp.card_number',
                    'tp.account_id',
                    'tp.payment_ref_no',
                    DB::raw('SUM(tp.amount) as total_paid')
                );

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $purchase->where('contacts.id', $customer_id);
            }
            if (!empty(request()->bill_no)) {
                $purchase->where('transactions.invoice_no', request()->bill_no);
            }
            if (!empty(request()->payment_ref_no)) {
                $purchase->where('tp.payment_ref_no', request()->payment_ref_no);
            }
            if (!empty(request()->cheque_number)) {
                $purchase->where('tp.cheque_number', request()->cheque_number);
            }
            if (!empty(request()->payment_type)) {
                $purchase->where('tp.method', request()->payment_type);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchase->whereDate('tp.paid_on', '>=', $start)
                    ->whereDate('tp.paid_on', '<=', $end);
            }
            $purchase->orderBy('tp.paid_on', 'desc')->groupBy('tp.id');
            
            $datatable = Datatables::of($purchase)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                        data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                                    
                        if (auth()->user()->can("purchase.view")) {
                            $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                        }
                    
                        // Other actions skipped for brevity/compatibility
                        $html .= '</ul></div>';
                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn('final_total', function ($row) use ($business_details) {
                    return '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="' . $row->final_total . '">' . $this->productUtil->num_f($row->final_total, false, $business_details, false) . '</span>';
                })
                ->editColumn('total_paid', function ($row) use ($business_details) {
                    if ($row->total_paid == '') {
                        $total_paid_html = '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="0.00">' . $this->productUtil->num_f(0, false, $business_details, false) . '</span>';
                    } else {
                        $total_paid_html = '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="' . $row->total_paid . '">' . $this->productUtil->num_f($row->total_paid, false, $business_details, false) . '</span>';
                    }
                    return $total_paid_html;
                })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('paid_on', '{{@format_date($paid_on)}}')
                ->editColumn('method', function ($row) {
                    $html = '';
                    if (strtolower($row->method) == 'bank_transfer' || strtolower($row->method) == 'direct_bank_deposit' || strtolower($row->method) == 'bank' || strtolower($row->method) == 'cheque') {
                        $html .= "Bank";

                        $bank_acccount = Account::find($row->account_id);
                        if (!empty($bank_acccount)) {
                            $html .= '<br><b>Bank Name:</b> ' . $bank_acccount->name . '</br>';
                        }
                        if(!empty($row->cheque_number)){
                            $html .= '<b>Cheque Number:</b> ' . $row->cheque_number . '</br>';
                        }
                        if(!empty($row->cheque_date)){
                            $html .= '<b>Cheque Date:</b> ' . $this->productUtil->format_date($row->cheque_date) . '</br>';
                        }

                    } else {
                        $html .= ucfirst(str_replace("_"," ",$row->method));
                    }

                    return $html;
                })
                ->editColumn('cheque_number', function ($row) {
                    if ($row->method == 'bank_transfer' || $row->method == 'cheque') {
                        return $row->cheque_number;
                    }
                    if ($row->method == 'card') {
                        return $row->card_number;
                    }
                    return '';
                })
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    return $invoice_no;
                });

            $rawColumns = ['method','final_total', 'action', 'total_paid', 'invoice_no'];
            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }
        return view('contacts::contact.issued_payment_details')->with(compact(
            'suppliers',
            'business_locations',
            'customers',
            'customer_group',
            'types',
            'payment_types',
            'bill_nos',
            'payment_ref_nos',
            'cheque_numbers'
        ));
    }

    public function getReturnedCheques()
    {
        $types = ['supplier', 'customer'];
        $business_id = request()->session()->get('user.business_id');
        $contacts = Contact::where('business_id', $business_id)->pluck('name','id');

        if (request()->ajax()) {
            $accounts = Transaction::where('transactions.type','cheque_return')
            ->leftJoin('transaction_payments', 'transactions.id', '=', 'transaction_payments.transaction_id')
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->where('transactions.business_id',$business_id)
            ->select(['contacts.name as customer','transaction_payments.cheque_number',
            'transaction_payments.cheque_date','transaction_payments.amount','transaction_payments.bank_name','transactions.transaction_date','contacts.type as contact_type']);

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $accounts->whereDate('transactions.transaction_date', '>=', request()->start_date);
                $accounts->whereDate('transactions.transaction_date', '<=', request()->end_date);
            }

            if (!empty(request()->contact_type)) {
                $accounts->where('contacts.type', request()->contact_type);
            }

            if (!empty(request()->user_id)) {
                $accounts->where('transactions.contact_id', request()->user_id);
            }
            if (!empty(request()->cheque_number)) {
                $accounts->where('transaction_payments.cheque_number', request()->cheque_number);
            }

            if (!empty(request()->bank_name)) {
                $accounts->where('transaction_payments.bank_name', request()->bank_name);
            }

            if (!empty(request()->amount)) {
                $accounts->where('transaction_payments.amount', request()->amount);
            }

            if (!empty(request()->cheque_date)) {
                $accounts->where('transaction_payments.cheque_date', request()->cheque_date);
            }


            return DataTables::of($accounts)
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('cheque_date', '{{@format_date($cheque_date)}}')
                ->addColumn('amount', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="false">' . $this->productUtil->num_f($row->amount) . '</span>';
                })
                ->removeColumn('id')
                ->rawColumns(['amount'])
                ->make(true);
        }

        return view('contacts::contact.returned_cheques')
            ->with(compact('types', 'contacts'));
    }
}
