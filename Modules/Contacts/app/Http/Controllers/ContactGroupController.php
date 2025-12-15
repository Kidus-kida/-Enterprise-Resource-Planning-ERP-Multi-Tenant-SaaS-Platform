<?php

namespace Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Contacts\Models\ContactGroup;
use Illuminate\Http\Request;
use Modules\Contacts\DataTables\ContactGroupDataTable;

class ContactGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ContactGroupDataTable $dataTable)
    {
        $pageTitle = __("Contact Groups");
        return $dataTable->render('contacts::contact_groups.index', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.contact_groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
        ]);

        $input = $request->except(['_token']);
        $input['business_id'] = 1;
        $input['created_by'] = auth()->id();
        
        // Use generic "both" if not specified, or force user to pick type
        // For now, let's assume if it's not present, it applies to both? 
        // Or cleaner: make it hidden input in form or select
        if(!isset($input['type'])) {
            $input['type'] = 'customer'; 
        }

        ContactGroup::create($input);

        $notification = notify(__('Contact Group created successfully'));
        return redirect()->route('contact-groups.index')->with($notification);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $contact_group = ContactGroup::findOrFail($id);
        return view('pages.contact_groups.edit', compact('contact_group'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $contact_group = ContactGroup::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
        ]);

        $input = $request->except(['_token', '_method']);
        $contact_group->update($input);

        $notification = notify(__('Contact Group updated successfully'));
        return redirect()->route('contact-groups.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        ContactGroup::destroy($id);
        $notification = notify(__('Contact Group deleted successfully'));
        return redirect()->route('contact-groups.index')->with($notification);
    }
}
