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
  
    public function index(Request $request)
    {
        $campaigns = Campaign::latest()->paginate(10);
        $pageTitle = __('Campaigns');
        return view('crm::campaigns.index', compact('campaigns', 'pageTitle'));
    }

  
    public function create()
    {
        return view('crm::campaigns.create');
    }

 
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:draft,active,paused,completed'
        ]);
        
        Campaign::create($request->all());
        $notification = notify(__("Campaign has been created"));
        return back()->with($notification);
    }

   
   
    public function show($id)
    {
        $campaign = Campaign::findOrFail(decrypt($id));
        $pageTitle = $campaign->title;
        return view('crm::campaigns.show', compact('campaign', 'pageTitle'));
    }
    
    public function edit(Campaign $campaign)
    {
        return view('crm::campaigns.edit', compact('campaign'));
    }

  
    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:draft,active,paused,completed'
        ]);
        
        $campaign->update($request->all());
        $notification = notify(__('Campaign has been updated'));
        return back()->with($notification);
    }

   
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        $notification = notify(__("Campaign has been deleted"));
        return back()->with($notification);
    }
}
