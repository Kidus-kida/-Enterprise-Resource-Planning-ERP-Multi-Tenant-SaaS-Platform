<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\Transaction;
use Modules\Contacts\Models\TransactionPayment;
use App\Models\Contact;
use Illuminate\Http\Request;
use Modules\Contacts\DataTables\CustomerPaymentDataTable;

class CustomerPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CustomerPaymentDataTable $dataTable)
    {
        $pageTitle = __("Customer Payments");
        return $dataTable->render('contacts::customer_payments.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Contact::where('type', 'customer')->pluck('name', 'id');
        return view('pages.customer_payments.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_for' => 'required|exists:contacts,id', // Customer
            'amount' => 'required|numeric',
            'paid_on' => 'required|date',
            'method' => 'required|string',
        ]);

        $input = $request->except(['_token']);
        $input['business_id'] = 1; 
        $input['created_by'] = auth()->id();
        
        // Logic: if this is a "Due Payment", we might need to find transactions to pay off. 
        // For "Customer Payments" module often implies receiving money.
        // For simplicity, we create a standalone payment record linked to the customer.
        
        TransactionPayment::create($input);

        $notification = notify(__('Payment recorded successfully'));
        return redirect()->route('customer-payments.index')->with($notification);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $payment = TransactionPayment::findOrFail($id);
        $customers = Contact::where('type', 'customer')->pluck('name', 'id');
        return view('pages.customer_payments.edit', compact('payment', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $payment = TransactionPayment::findOrFail($id);
        
        $request->validate([
            'amount' => 'required|numeric',
            'paid_on' => 'required|date',
            'method' => 'required|string',
        ]);

        $input = $request->except(['_token', '_method']);
        $payment->update($input);

        $notification = notify(__('Payment updated successfully'));
        return redirect()->route('customer-payments.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        TransactionPayment::destroy($id);
        $notification = notify(__('Payment deleted successfully'));
        return redirect()->route('customer-payments.index')->with($notification);
    }
}
