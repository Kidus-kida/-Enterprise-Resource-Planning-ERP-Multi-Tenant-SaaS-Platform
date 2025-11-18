<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Crm\Models\FollowUp;
use Modules\Crm\Models\Lead;

class FollowUpController extends Controller
{
    public function index()
    {
        $followUps = FollowUp::with('lead')->latest()->paginate(10);
        $pageTitle = __('Follow-ups');
        return view('crm::follow-ups.index', compact('followUps', 'pageTitle'));
    }

    public function create()
    {
        $leads = Lead::all();
        $users = \App\Models\User::all();
        return view('crm::follow-ups.create', compact('leads', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'follow_up_date' => 'required|date',
            'status' => 'required|in:pending,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        FollowUp::create($request->all() + ['created_by' => auth()->id()]);
        
        $notification = notify(__('Follow-up created successfully'));
        return back()->with($notification);
    }

    public function show($id)
    {
        $followUp = FollowUp::findOrFail(decrypt($id));
        $pageTitle = $followUp->title;
        return view('crm::follow-ups.show', compact('followUp', 'pageTitle'));
    }

    public function edit(FollowUp $followUp)
    {
        $leads = Lead::all();
        $users = \App\Models\User::all();
        return view('crm::follow-ups.edit', compact('followUp', 'leads', 'users'));
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'follow_up_date' => 'required|date',
            'status' => 'required|in:pending,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $followUp->update($request->all());
        
        $notification = notify(__('Follow-up updated successfully'));
        return back()->with($notification);
    }

    public function destroy(FollowUp $followUp)
    {
        $followUp->delete();
        
        $notification = notify(__('Follow-up deleted successfully'));
        return back()->with($notification);
    }
}