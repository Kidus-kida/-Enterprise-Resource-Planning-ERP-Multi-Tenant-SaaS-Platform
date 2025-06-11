<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Folder;
class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    // List all folders/files
    // $folders = Folder::all();
    //return view("pages.file-management.files");
}

    /**
     * Show the form for creating a new resource.
     */
   
public function create(Request $request)
{
    return view('pages.file-management.create-file');
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
{
    $folder = Folder::findOrFail($id);
    $files = []; // Get files for this folder
    return view("pages.file-management.files", compact('folder', 'files'));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
