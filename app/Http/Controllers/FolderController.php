<?php

namespace App\Http\Controllers;

use App\DataTables\FolderDataTable;
use Illuminate\Http\Request;
use App\Models\Folder;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FolderDataTable $dataTable )
    {
        return $dataTable->render("pages.file-management.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.file-management.create-folder');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        Folder::create([
            'name' => $request->name,
        ]);
        $notification = notify('Folder has been created');
        return redirect()->route('folders.index')->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('pages.departments.edit',compact(
            'department',
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
