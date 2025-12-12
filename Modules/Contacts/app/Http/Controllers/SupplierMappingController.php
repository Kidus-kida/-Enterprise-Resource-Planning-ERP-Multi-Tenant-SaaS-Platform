<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\SupplierProductMapping;
use App\Models\Contact;
// use App\Models\Product; // Assuming Product Exists or we mock it
use Illuminate\Http\Request;
use Modules\Contacts\DataTables\SupplierMappingDataTable;

class SupplierMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SupplierMappingDataTable $dataTable)
    {
        $pageTitle = __("Supplier Product Mappings");
        return $dataTable->render('contacts::supplier_mappings.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Contact::where('type', 'supplier')->pluck('name', 'id');
        // $products = Product::pluck('name', 'id'); // Use real products
        $products = [1 => 'Demo Product A', 2 => 'Demo Product B']; // Placeholder
        return view('pages.supplier_mappings.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:contacts,id',
            'product_id' => 'required', // |exists:products,id
        ]);

        $input = $request->except(['_token']);
        
        // Check for duplicates
        $exists = SupplierProductMapping::where('supplier_id', $input['supplier_id'])
                    ->where('product_id', $input['product_id'])
                    ->exists();

        if($exists){
             $notification = notify(__('Mapping already exists'), 'error');
             return redirect()->back()->with($notification);
        }

        SupplierProductMapping::create($input);

        $notification = notify(__('Mapping created successfully'));
        return redirect()->route('supplier-mappings.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        SupplierProductMapping::destroy($id);
        $notification = notify(__('Mapping deleted successfully'));
        return redirect()->route('supplier-mappings.index')->with($notification);
    }
}
