<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\DataTables\ContactDataTable;
use Illuminate\Support\Facades\Crypt;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ContactDataTable $dataTable)
    {
        $pageTitle = __("Contacts");
        return $dataTable->render('pages.contacts.index', compact(
            'pageTitle'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = __("Add Contact");
        $types = Contact::typeDropdown(false);
        return view('pages.contacts.create', compact('pageTitle', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'supplier_business_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:255',
        ]);

        $input = $request->except(['_token']);
        $input['business_id'] = 1; // Default
        $input['created_by'] = auth()->id();
        
        // Generate contact_id if needed, or leave nullable
        $input['contact_id'] = 'CO-' . time(); 

        Contact::create($input);

        $notification = notify(__('Contact has been created'));
        return redirect()->route('contacts.index')->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Simple show, or redirect to edit
        // $contact = Contact::findOrFail($id);
        // return view('pages.contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            // Decrypt if passing encrypted ID, or just use ID if routes are resource
            // ClientsController used Query params with encryption, but here standard resource route uses basic ID usually.
            // But if I follow ClientsController pattern:
            //$id = Crypt::decrypt($id);
            
            $contact = Contact::findOrFail($id);
            $pageTitle = __("Edit Contact");
            $types = Contact::typeDropdown(false);

            return view('pages.contacts.edit', compact('contact', 'pageTitle', 'types'));
        } catch (\Exception $e) {
             $notification = notify(__($e->getMessage()),'error');
            return back()->with($notification);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
        ]);

        $input = $request->except(['_token', '_method']);
        $contact->update($input);

        $notification = notify(__('Contact has been updated'));
        return redirect()->route('contacts.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Contact::destroy($id);
        $notification = notify(__('Contact has been deleted'));
        return redirect()->route('contacts.index')->with($notification);
    }
}
