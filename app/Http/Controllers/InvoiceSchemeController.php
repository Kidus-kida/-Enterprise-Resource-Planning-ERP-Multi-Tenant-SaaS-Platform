<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\InvoiceLayout;
use App\InvoiceScheme;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvoiceSchemeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = 1; // Default
        
        if (request()->ajax()) {
            $schemes = InvoiceScheme::where('business_id', $business_id)
                            ->select(['id', 'name', 'scheme_type', 'prefix', 'start_number', 'invoice_count', 'total_digits', 'is_default']);

            return Datatables::of($schemes)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                        data-bs-toggle="dropdown" aria-expanded="false">' .
                                        __("messages.action") .
                                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" role="menu">
                                        <li><a href="' . action([\App\Http\Controllers\InvoiceSchemeController::class, 'edit'], [$row->id]) . '" class="dropdown-item btn-modal" data-container=".invoice_modal"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>';
                        
                        if ($row->is_default) {
                            $html .= '<li><a href="#" class="dropdown-item disabled"><i class="fa fa-check-square-o"></i> ' . __("Default") . '</a></li>';
                        } else {
                            $html .= '<li><a href="' . action([\App\Http\Controllers\InvoiceSchemeController::class, 'setDefault'], [$row->id]) . '" class="dropdown-item set_default_invoice"><i class="fa fa-check"></i> ' . __("Set as Default") . '</a></li>';
                            $html .= '<li><a href="' . action([\App\Http\Controllers\InvoiceSchemeController::class, 'destroy'], [$row->id]) . '" class="dropdown-item delete_invoice_button"><i class="glyphicon glyphicon-trash"></i> ' . __("messages.delete") . '</a></li>';
                        }
                        
                        $html .= '</ul></div>';
                        return $html;
                    }
                )
                ->editColumn('prefix', function ($row) {
                    if ($row->scheme_type == 'year') {
                        return date('Y') . '-';
                    } else {
                        return $row->prefix;
                    }
                })
                ->editColumn('name', function ($row) {
                    if ($row->is_default == 1) {
                        return $row->name . ' &nbsp; <span class="badge bg-success">' . __("Default") .'</span>';
                    } else {
                        return $row->name;
                    }
                })
                ->removeColumn('id')
                ->removeColumn('is_default')
                ->removeColumn('scheme_type')
                ->rawColumns(['action', 'name'])
                ->make(true);
        }

        $invoice_layouts = InvoiceLayout::where('business_id', $business_id)->get();

        return view('settings.invoice.index', compact('invoice_layouts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('settings.invoice.scheme.create');
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
            $input = $request->only(['name', 'scheme_type', 'prefix', 'start_number', 'total_digits']);
            $business_id = 1;
            $input['business_id'] = $business_id;

            if (!empty($request->input('is_default'))) {
                // Reset other defaults
                InvoiceScheme::where('business_id', $business_id)
                                ->where('is_default', 1)
                                ->update(['is_default' => 0]);
                $input['is_default'] = 1;
            }
            
            InvoiceScheme::create($input);
            
            $output = ['success' => true,
                            'msg' => __("Invoice scheme added successfully")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_id = 1;
        $invoice = InvoiceScheme::where('business_id', $business_id)->find($id);

        return view('settings.invoice.scheme.edit', compact('invoice'));
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
        try {
            $input = $request->only(['name', 'scheme_type', 'prefix', 'start_number', 'total_digits']);
            
            $business_id = 1;
            
            if (!empty($request->input('is_default'))) {
                InvoiceScheme::where('business_id', $business_id)
                                ->where('is_default', 1)
                                ->update(['is_default' => 0]);
                $input['is_default'] = 1;
            }

            InvoiceScheme::where('id', $id)->update($input);

            $output = ['success' => true,
                            'msg' => __('Invoice scheme updated successfully')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
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
                $invoice = InvoiceScheme::find($id);
                if ($invoice->is_default != 1) {
                    $invoice->delete();
                    $output = ['success' => true,
                                'msg' => __("Invoice scheme deleted successfully")
                                ];
                } else {
                    $output = ['success' => false,
                                'msg' => __("Cannot delete default scheme")
                                ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output; // Return JSON or boolean for DataTables
        }
    }

    public function setDefault($id)
    {
        if (request()->ajax()) {
            try {
                $business_id = 1;
                InvoiceScheme::where('business_id', $business_id)
                                ->where('is_default', 1)
                                ->update(['is_default' => 0]);
                                 
                $invoice = InvoiceScheme::find($id);
                $invoice->is_default = 1;
                $invoice->save();

                $output = ['success' => true,
                            'msg' => __("Default set successfully")
                        ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }
            return $output;
        }
    }
}
