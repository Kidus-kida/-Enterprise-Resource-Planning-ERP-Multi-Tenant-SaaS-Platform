<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\SupplierProductMapping;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Product; // Real Product model now
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = __("Add Supplier Map Products");
        $name = Contact::where('type', 'supplier')->pluck('name', 'id');
        $type = null; // Default selected
        return view('contacts::supplier_mappings.index', compact('pageTitle', 'name', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Contact::where('type', 'supplier')->pluck('name', 'id');
        $products = Product::not_modifier()->pluck('name', 'id'); 
        return view('contacts::supplier_mappings.create', compact('suppliers', 'products'));
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

    public function getSupplierMapped(Request $request) 
    {
        $supplier_id = $request->supplier_id;
        $business_id = auth()->user()->business_id;

        // Get all products for the business
        // Assuming 'business_id' column exists in products table. 
        // If Product model doesn't block by global scope, we add where clause.
        $all_products = Product::where('business_id', $business_id)
            ->where('type', '!=', 'modifier')
            ->pluck('name', 'id')
            ->toArray();

        // Get already mapped products for this supplier
        $mapped_ids = SupplierProductMapping::where('supplier_id', $supplier_id)
            ->pluck('product_id')
            ->toArray();

        $names = []; // Available (Unmapped)
        $mappingnames = []; // Already Mapped

        foreach ($all_products as $id => $name) {
            if (in_array($id, $mapped_ids)) {
                $mappingnames[$id] = $name;
            } else {
                $names[$id] = $name;
            }
        }

        return response()->json([
            'names' => $names,
            'mappingnames' => $mappingnames
        ]);
    }

    public function createMappings(Request $request)
    {
         $supplier_id = $request->input('type'); // 'type' is used as supplier key in source form
         $mapped_ids = $request->input('ss_umimp_list'); // This is the ID list of Mapped Items
         
         if(empty($supplier_id)){
             return response()->json(['success' => false, 'msg' => 'Supplier not selected']);
         }

         // Transaction for safety
         DB::beginTransaction();
         try {
             // Clear existing mappings
             SupplierProductMapping::where('supplier_id', $supplier_id)->delete();
             
             if(!empty($mapped_ids)){
                 foreach($mapped_ids as $pid){
                     SupplierProductMapping::create([
                         'supplier_id' => $supplier_id,
                         'product_id' => $pid
                     ]);
                 }
             }
             DB::commit();
             return response()->json(['success' => true]);
         } catch(\Exception $e) {
             DB::rollBack();
             return response()->json(['success' => false, 'msg' => $e->getMessage()]);
         }
    }
}
