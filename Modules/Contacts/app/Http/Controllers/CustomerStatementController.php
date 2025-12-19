<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\CustomerStatement;
use Modules\Contacts\Models\CustomerStatementDetail;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Transaction;
use App\Models\Business;
use App\Models\BusinessLocation;
use Illuminate\Http\Request;
use Modules\Contacts\DataTables\CustomerStatementDataTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Utils\Util;
use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerStatementController extends Controller
{
    protected $commonUtil;
    protected $transactionUtil;
    protected $productUtil;

    public function __construct(
        Util $commonUtil,
        TransactionUtil $transactionUtil,
        ProductUtil $productUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CustomerStatementDataTable $dataTable)
    {
        $pageTitle = __("Customer Statements");
        return $dataTable->render('contacts::customer_statements.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::where('business_id', $business_id)->where('type', 'customer')->pluck('name', 'id');
        return view('contacts::customer_statements.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'date_from' => 'required',
            'date_to' => 'required',
        ]);

        try {
            $business_id = auth()->user()->business_id;
            
            DB::beginTransaction();

            $cust_stm = CustomerStatement::orderBy('id', 'desc')->where('business_id', $business_id)->first();
            
            $customer = Contact::find($request->customer_id);
            // Assuming name is 'FirstName LastName' or similar, we extract a suffix or logic.
            // Simplified logic as per previous source snippet or generic behavior:
            $name_parts = explode(' ', $customer->name);
            $suffix = '';
            foreach($name_parts as $part){
                $suffix .= strtoupper(substr($part, 0, 1));
            }

            if (!empty($cust_stm)) {
                $num = explode('-', $cust_stm->statement_no);
                $statement_no = $suffix . '-' . (((int)end($num)) + 1);
            } else {
                $statement_no = $suffix . '-1';
            }

            $date_from = $request->date_from;
            $date_to = $request->date_to;

            $statement_data = [
                'business_id' => $business_id,
                'statement_no' => $statement_no,
                'print_date' => date('Y-m-d'),
                'date_from' => $date_from,
                'date_to' => $date_to,
                'customer_id' => $request->customer_id,
                'added_by' => auth()->user()->id
            ];

            $statement = CustomerStatement::create($statement_data);

            $transactions = $this->__getStatement($business_id, $request->customer_id, $date_from, $date_to);

            foreach ($transactions as $transaction) {
                 CustomerStatementDetail::create([
                    'business_id' => $business_id,
                    'statement_id' => $statement->id,
                    'date' => $transaction->transaction_date,
                    'location' => $transaction->location ?? '', 
                    'invoice_no' => $transaction->invoice_no,
                    'customer_reference' => $transaction->ref_no, // assuming aliased in __getStatement
                    'invoice_amount' => $transaction->final_total,
                    'payment_received' => $transaction->total_paid,
                    'due_amount' => $transaction->due_amount ?? ($transaction->final_total - $transaction->total_paid)
                ]);
            }
            
            DB::commit();

            $output = ['success' => 1, 'msg' => 'Statement Created Successfully'];
            return redirect()->route('customer-statements.index')->with('status', $output);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = ['success' => 0, 'msg' => $e->getMessage()];
            return redirect()->back()->with('status', $output);
        }
    }

    protected function __getStatement($business_id, $customer_id, $date_from, $date_to)
    {
        // Fetch transactions
        // Adjusted logic to match standard TewosHR/ERP structure
        $query = Transaction::where('transactions.business_id', $business_id)
                    ->where('contacts.id', $customer_id)
                    ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->leftJoin('business_locations', 'transactions.location_id', '=', 'business_locations.id')
                    ->whereBetween('transaction_date', [$date_from, $date_to])
                    ->select(
                        'transactions.*',
                        'business_locations.name as location',
                        'transactions.invoice_no as invoice_no',
                        // 'transactions.ref_no' - assuming column exists or we use invoice_no
                        DB::raw("IFNULL(transactions.ref_no, transactions.invoice_no) as ref_no"),
                         // Calculate paid amount
                        DB::raw("(SELECT SUM(IF(tp.is_return = 1,-1*tp.amount,tp.amount)) FROM transaction_payments as tp WHERE tp.transaction_id=transactions.id) as total_paid")
                    );
        
        return $query->get();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $statement = CustomerStatement::findOrFail($id);
            $statement->details()->delete(); // Delete details first
            $statement->delete();
            $output = ['success' => 1, 'msg' => 'Statement Deleted Successfully'];
        } catch(\Exception $e) {
             $output = ['success' => 0, 'msg' => $e->getMessage()];
        }
        return redirect()->route('customer-statements.index')->with('status', $output);
    }
}
