<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Award;
use App\Enums\UserType;
use App\Models\User;
use App\DataTables\AwardDataTable;



class AwardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-award')->only('index');
        $this->middleware('permission:create-award')->only(['create', 'store']);
        $this->middleware('permission:edit-award')->only(['edit', 'update']);
        $this->middleware('permission:delete-award')->only('destroy');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(AwardDataTable $dataTable)
    {
        $pageTitle = __("Awards");
        return $dataTable->render('pages.awards.index',compact(
            'pageTitle'
        ));
        // $awards = Award::with(['recipient', 'hr'])->latest()->get();
        // return view('pages.awards.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('type', UserType::EMPLOYEE)->whereIsActive(true)->get();
        return view('pages.awards.create',compact(
            'users'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'awarded_by' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'award_type' => 'required|string', 
            'awarded_at' => 'required|date',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png', // allow certain file types
            
        ]);

        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store('certificates', 'public');
            $validated['award_file'] = $path; 
        }

        Award::create($validated);

        $notification = notify(__('Award created successfully'));
        return back()->with($notification);

        // return response()->json(['message' => 'Award created successfully', 'data' => $award], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Award $award)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $award = Award::findOrFail($id);
        $users = User::where('type', UserType::EMPLOYEE)->whereIsActive(true)->get();
        return view('pages.awards.edit',compact(
            'users','award'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Award $award)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'awarded_by' => 'sometimes|string',
            'award_type' => 'sometimes|string', 
            'awarded_at' => 'sometimes|date',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            
        ]);

        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store('certificates', 'public');
            $validated['award_file'] = $path; 
        }

        $award->update($validated);

        $notification = notify(__('Award has been updated'));
        return back()->with($notification);

        // return response()->json(['message' => 'Award updated', 'data' => $award]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Award $award)
    {
        $award->delete();
        $notification = notify(__('Award has been Deleted'));
        return back()->with($notification);
    }
}
