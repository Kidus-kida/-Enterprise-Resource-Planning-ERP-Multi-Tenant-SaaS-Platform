<?php

namespace Modules\Products\Http\Controllers;

use App\BusinessLocation;
use App\Category;
use App\VariationTemplate;
use App\VariationValueTemplate;
use App\ProductVariation;
use App\Variation;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;
use DB;

class VariationTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = auth()->user()->business_id;

        if (request()->ajax()) {
            \Log::info('Variations AJAX requested for business_id: ' . $business_id);
            $query = VariationTemplate::with(['values'])
                ->select('variation_templates.*');

            if (!empty($business_id)) {
                $query->where('variation_templates.business_id', $business_id);
            }

            $query->orderBy('variation_templates.name', 'asc');
            \Log::info('Variations query count: ' . (clone $query)->count());

            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    $action = '<button data-url="' . route('products.variations.edit', [$row->id]) . '" data-ajax-modal="true" data-title="Edit Variation" class="btn btn-xs btn-primary edit_variation_button"><i class="fa fa-edit"></i> Edit</button>';

                    $action .= '&nbsp;<button data-href="' . route('products.variations.destroy', [$row->id]) . '" class="btn btn-xs btn-danger delete_variation_button"><i class="fa fa-trash"></i> Delete</button>';

                    return $action;
                })
                ->addColumn('values', function ($row) {
                    return $row->values->pluck('name')->implode(', ') ?: '';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $categories = Category::forDropdown($business_id);
        $sub_categories = Category::subCategoryforDropdown($business_id);

        return view('products::variation.index')->with(compact(
            'business_locations',
            'categories',
            'sub_categories',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('products::variation.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input = $request->only(['name']);
            $input['business_id'] = auth()->user()->business_id;
            $input['created_by'] = auth()->user()->id;

            $variation = VariationTemplate::create($input);

            // Create variation values
            if (!empty($request->input('variation_values'))) {
                $values = $request->input('variation_values');
                $data = [];
                foreach ($values as $value) {
                    if (!empty($value)) {
                        $data[] = ['name' => $value];
                    }
                }
                $variation->values()->createMany($data);
            }

            $output = [
                'success' => true,
                'msg' => 'Variation added succesfully'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'Something went wrong, please try again'
            ];
        }

        return response()->json($output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(['success' => false, 'msg' => 'Not implemented']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (request()->ajax()) {
            $business_id = auth()->user()->business_id;
            $variation = VariationTemplate::where('business_id', $business_id)
                ->with(['values'])->findOrFail($id);

            return view('products::variation.edit')
                ->with(compact('variation'));
        }
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (request()->ajax()) {
            try {
                $input = $request->only(['name']);
                $business_id = auth()->user()->business_id;

                $variation = VariationTemplate::where('business_id', $business_id)->findOrFail($id);

                if ($variation->name != $input['name']) {
                    $variation->name = $input['name'];
                    $variation->save();

                    ProductVariation::where('variation_template_id', $variation->id)
                        ->update(['name' => $variation->name]);
                }

                // Update existing variation values
                $edit_variation_values = $request->input('edit_variation_values');
                $updated_value_ids = [];
                if (!empty($edit_variation_values)) {
                    foreach ($edit_variation_values as $key => $value) {
                        if (!empty($value)) {
                            $variation_val = VariationValueTemplate::find($key);
                            if ($variation_val) {
                                if ($variation_val->name != $value) {
                                    $variation_val->name = $value;
                                    $variation_val->save();

                                    // Update related variations if name changed
                                    Variation::where('variation_value_id', $key)
                                        ->update(['name' => $value]);
                                }
                                $updated_value_ids[] = $key;
                            }
                        }
                    }
                }

                // Remove values that were deleted from the form
                $variation->values()->whereNotIn('id', $updated_value_ids)->delete();

                // Add new variation values
                $variation_values = $request->input('variation_values');
                if (!empty($variation_values)) {
                    $new_values = [];
                    foreach ($variation_values as $value) {
                        if (!empty($value)) {
                            $new_values[] = ['name' => $value];
                        }
                    }
                    $variation->values()->createMany($new_values);
                }

                $output = [
                    'success' => true,
                    'msg' => 'Variation updated succesfully'
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => 'Something went wrong, please try again'
                ];
            }

            return response()->json($output);
        }
        return response()->json(['success' => false, 'msg' => 'Invalid request']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            try {
                $business_id = auth()->user()->business_id;

                $variation = VariationTemplate::where('business_id', $business_id)->findOrFail($id);
                $variation->delete();

                $output = [
                    'success' => true,
                    'msg' => 'Variation deleted succesfully'
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => 'Something went wrong, please try again'
                ];
            }

            return response()->json($output);
        }
        return response()->json(['success' => false, 'msg' => 'Invalid request']);
    }
}
