<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Crm\Models\Lead;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::latest()->paginate(10);
        $pageTitle = __('Leads');
        return view('crm::leads.index', compact('leads', 'pageTitle'));
    }

    public function create()
    {
        return view('crm::leads.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'company' => 'nullable|string',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
            'source' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        Lead::create($request->all() + ['created_by' => auth()->id()]);
        
        $notification = notify(__('Lead created successfully'));
        return back()->with($notification);
    }

    public function show($id)
    {
        $lead = Lead::findOrFail(decrypt($id));
        $pageTitle = $lead->name;
        return view('crm::leads.show', compact('lead', 'pageTitle'));
    }

    public function edit(Lead $lead)
    {
        return view('crm::leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'company' => 'nullable|string',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
            'source' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $lead->update($request->all());
        
        $notification = notify(__('Lead updated successfully'));
        return back()->with($notification);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        
        $notification = notify(__('Lead deleted successfully'));
        return back()->with($notification);
    }
}