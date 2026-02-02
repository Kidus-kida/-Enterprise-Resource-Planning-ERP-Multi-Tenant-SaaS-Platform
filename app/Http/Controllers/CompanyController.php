<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Company;
use App\Services\CompanyService;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    protected $companyService;
    protected $moduleUtil;

    public function __construct(CompanyService $companyService, ModuleUtil $moduleUtil)
    {
        $this->companyService = $companyService;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = auth()->user()->business_id ?? request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $companies = Company::select(['id', 'name', 'tax_number', 'is_default', 'is_active']);

            return Datatables::of($companies)
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
                                <li><a href="' . url('/settings/multi-companies/' . $row->id . '/edit') . '" class="dropdown-item edit_company_button"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>
                                <li><a href="javascript:void(0);" data-href="' . url('/settings/multi-companies/' . $row->id) . '" class="dropdown-item delete_company_button"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</a></li>
                            </ul></div>';
                        return $html;
                    }
                )
                ->editColumn('is_default', function($row) {
                    return $row->is_default ? '<span class="label bg-green">Yes</span>' : '<span class="label bg-gray">No</span>';
                })
                ->rawColumns(['action', 'is_default'])
                ->make(true);
        }

        return view('company.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('company.create');
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
            $business_id = auth()->user()->business_id ?? request()->session()->get('user.business_id');
            
            // Always enforce limits via service
            $this->companyService->checkCompanyLimit($business_id);
            
            $input = $request->only(['name', 'tax_number']);
            $input['business_id'] = $business_id;
            
            // Create company
            Company::create($input);

            $output = [
                'success' => true,
                'msg' => __("company.added_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage() // Show limit message to user
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
        $business_id = auth()->user()->business_id ?? request()->session()->get('user.business_id');
        $company = Company::findOrFail($id);

        return view('company.edit', compact('company'));
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
            $company = Company::findOrFail($id);
            $input = $request->only(['name', 'tax_number']);
            $company->update($input);

            $output = [
                'success' => true,
                'msg' => __("company.updated_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
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
        try {
            $business_id = auth()->user()->business_id ?? request()->session()->get('user.business_id');
            $company = Company::findOrFail($id);

            if ($company->is_default) {
                throw new \Exception("Cannot delete default company.");
            }
            
            // Check if used in locations
            if ($company->business_locations()->exists()) {
                throw new \Exception("Cannot delete company with assigned locations.");
            }

            $company->delete();

            $output = [
                'success' => true,
                'msg' => __("company.deleted_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }

        return $output;
    }

    /**
     * Switch active company.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function switch($id)
    {
        // Allow users who can manage business settings to switch companies
        if (!auth()->user()->can('business_settings.access') && !auth()->user()->isTenantOwner()) {
            return redirect()->back()->with('error', 'You do not have permission to switch companies.');
        }

        try {
            $business_id = auth()->user()->business_id ?? request()->session()->get('user.business_id');
            $company = Company::findOrFail($id);

            request()->session()->put('user.company_id', $company->id);
            // Also update active_company_ids to include just this one for consistency
            request()->session()->put('user.active_company_ids', [$company->id]);

            return redirect()->back()->with('status', 'Company switched to ' . $company->name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Switch to multiple active companies.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function switchMultiple(Request $request)
    {
        // Allow users who can manage business settings to switch companies
        if (!auth()->user()->can('business_settings.access') && !auth()->user()->isTenantOwner()) {
            return redirect()->back()->with('error', 'You do not have permission to switch companies.');
        }

        try {
            $ids = $request->input('company_ids', []);
            
            // If empty, it means "All Companies" or "Reset"? 
            // User requested "check all boxes... show data from selected companies"
            // If none checked, maybe show nothing or All? Let's assume none = nothing (or reset to default).
            // Actually, if they uncheck everything, they see nothing?
            // Let's validate IDs belong to business
            if (!empty($ids)) {
                $valid_ids = Company::whereIn('id', $ids)->pluck('id')->toArray();
                request()->session()->put('user.active_company_ids', $valid_ids);
                
                // Set first one as primary for strictly single-company logic fallback
                request()->session()->put('user.company_id', $valid_ids[0]);
                
                $msg = 'Active view updated to ' . count($valid_ids) . ' companies.';
            } else {
                request()->session()->forget('user.active_company_ids');
                request()->session()->forget('user.company_id');
                $msg = 'No companies selected.';
            }

            return redirect()->back()->with('status', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
