<?php

namespace App\Http\Controllers;

use App\Models\TaxCalculation;
use Illuminate\Http\Request;
use App\DataTables\TaxCalculationDataTable;


class TaxCalculationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TaxCalculationDataTable $dataTable)
    {
        //
          $pageTitle = __('Tax Calculation Settings');
    
        return $dataTable->render('pages.settings.tax management.index',compact(
            'pageTitle',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view("pages.settings.tax management.create");
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
         $request->validate([
            'salary_from'      => 'required|numeric|min:0',
            'salary_to'        => 'nullable|numeric|gte:salary_from',
            'percentage'       => 'required|numeric|min:0|max:100',
            'deducted_amount'  => 'required|numeric|min:0',
        ]);
        

        TaxCalculation::create($request->all());
     $notification = notify('Tax Range have been created');
        return redirect()
            ->route('payroll.tax.index')
            ->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(TaxCalculation $tax)
    {
        //
        return view('pages.settings.tax management.show', compact('tax'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    

    /**
     * Update the specified resource in storage.
     */
   public function edit(TaxCalculation $tax)
{
    return view('pages.settings.tax management.edit', compact('tax'));
}

public function update(Request $request, TaxCalculation $tax)
{
    $request->validate([
        'salary_from'      => 'required|numeric|min:0',
        'salary_to'        => 'nullable|numeric|gte:salary_from',
        'percentage'       => 'required|numeric|min:0|max:100',
        'deducted_amount'  => 'required|numeric|min:0',
    ]);

    $tax->update($request->all());

    return redirect()
        ->route('payroll.tax.index')
        ->with(notify('Tax range updated successfully.'));
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxCalculation $tax)
    {
        //
         $tax->delete();

     $notification = notify('Tax Range have been deleted');
        return redirect()
            ->route('payroll.tax.index')
            ->with($notification);
    
    }
}
