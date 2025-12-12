<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\CustomerStatement;
use Modules\Contacts\Models\CustomerStatementDetail;
use App\Models\Contact;
use Modules\Contacts\Models\Transaction; // Needed to fetch data for statement
use Illuminate\Http\Request;
use Modules\Contacts\DataTables\CustomerStatementDataTable;
use Carbon\Carbon;

class CustomerStatementController extends Controller
{
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
        $customers = Contact::where('type', 'customer')->pluck('name', 'id');
        return view('pages.customer_statements.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:contacts,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $business_id = 1; // Default
        
        // 1. Generate Statement No
        $cust = Contact::findOrFail($request->customer_id);
        $abbr = strtoupper(substr($cust->name, 0, 2));
        $lastStatement = CustomerStatement::where('customer_id', $request->customer_id)
            ->latest()
            ->first();
            
        $nextNum = 1;
        if($lastStatement && strpos($lastStatement->statement_no, '-') !== false){
            $parts = explode('-', $lastStatement->statement_no);
            if(isset($parts[1])) $nextNum = (int)$parts[1] + 1;
        }
        $statementNo = $abbr . '-' . $nextNum;

        // 2. Create Header
        $statement = CustomerStatement::create([
            'business_id' => $business_id,
            'statement_no' => $statementNo,
            'customer_id' => $request->customer_id,
            'print_date' => now(),
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'added_by' => auth()->id(),
        ]);
        
        // 3. Dig for transactions to populate details
        // Logic similar to ERP's __getStatement method
        // For now, we will fetch transactions for this customer in range
        $transactions = Transaction::where('contact_id', $request->customer_id)
            ->whereBetween('transaction_date', [$request->date_from, $request->date_to])
            ->get();
            
        foreach($transactions as $txn) {
            
            // Calculate due, paid, etc.
             $final_total = $txn->final_total; // Or calculated based on product lines
             $paid = 0; // fetch payments
             // $paid = $txn->payments->sum('amount');
             $due = $final_total - $paid;

             CustomerStatementDetail::create([
                 'business_id' => $business_id,
                 'statement_id' => $statement->id,
                 'date' => $txn->transaction_date,
                 'location' => 'Main', // $txn->location->name
                 'invoice_no' => $txn->invoice_no,
                 'invoice_amount' => $final_total,
                 'due_amount' => $due,
                 // other fields as per ERP logic
             ]);
        }

        $notification = notify(__('Statement generated successfully'));
        return redirect()->route('customer-statements.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $statement = CustomerStatement::findOrFail($id);
        $statement->details()->delete();
        $statement->delete();
        
        $notification = notify(__('Statement deleted successfully'));
        return redirect()->route('customer-statements.index')->with($notification);
    }
}
