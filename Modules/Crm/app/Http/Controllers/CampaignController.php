<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Crm\Models\Campaign;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageTitle = __('Campaigns');
        if($request->ajax()){
            $categories = Campaign::get();
            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('action',function ($row){
                    $id = $row->id;
                    return view('crm::categories.actions',compact(
                        'id'
                    ));
                })
                ->rawColumns(['action'])
                ->make();
        }
        return view('crm::categories.index',compact(
            'pageTitle'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('crm::categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);
        Campaign::create([
            'title' => $request->title,
            'description' => $request->description
        ]);
        $notification = notify(__("Campaign has been created"));
        return back()->with($notification);
    }

   
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $category, $id)
    {
        $category = Campaign::findOrFail($id);
        return view('crm::categories.edit',compact(
            'category'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        
        $request->validate([
            'title' => 'required',
        ]);
        $category = Campaign::findOrFail($id);
        $category->update([
            'title' => $request->title,
            'description' => $request->description
        ]);
        $notification = notify(__('Campaign has been updated'));
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $category, $id)
    {
        $category = Campaign::findOrFail($id);
        $category->delete();
        $notification = notify(__("Campaign has been deleted"));
        return back()->with($notification);
    }
}
