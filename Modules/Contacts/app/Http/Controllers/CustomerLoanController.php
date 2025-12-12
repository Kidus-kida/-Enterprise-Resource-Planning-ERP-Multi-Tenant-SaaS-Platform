<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\Transaction;
use App\Models\Contact;
use Illuminate\Http\Request;
use Modules\Contacts\DataTables\CustomerLoanDataTable;

class CustomerLoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CustomerLoanDataTable $dataTable)
    {
        $pageTitle = __("Customer Loans");
        return $dataTable->render('contacts::customer_loans.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Contact::where('type', 'customer')->pluck('name', 'id');
        return view('pages.customer_loans.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'final_total' => 'required|numeric',
            'transaction_date' => 'required|date',
        ]);

        $input = $request->except(['_token']);
        $input['business_id'] = 1; // Default
        $input['created_by'] = auth()->id();
        $input['type'] = 'direct_customer_loan';
        $input['status'] = 'final';
        $input['payment_status'] = 'due'; 

        Transaction::create($input);

        $notification = notify(__('Customer Loan created successfully'));
        return redirect()->route('customer-loans.index')->with($notification);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id);
        $customers = Contact::where('type', 'customer')->pluck('name', 'id');
        return view('pages.customer_loans.edit', compact('transaction', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $request->validate([
            'final_total' => 'required|numeric',
            'transaction_date' => 'required|date',
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $input = $request->except(['_token', '_method']);
        $transaction->update($input);

        $notification = notify(__('Customer Loan updated successfully'));
        return redirect()->route('customer-loans.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Transaction::destroy($id);
        $notification = notify(__('Customer Loan deleted successfully'));
        return redirect()->route('customer-loans.index')->with($notification);
    }
}
