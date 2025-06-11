<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Folder;
use App\Models\File;
class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    // List all files
     $files= Files::all();
    return view("pages.file-management.files",compact('files'));
}

    /**
     * Show the form for creating a new resource.
     */
   
public function create(Request $request)
{
    $request->validate([
        'folder' => 'required|exists:folders,id'
    ]);

    $folder = Folder::findOrFail($request->folder);
    
    return view('pages.file-management.create-file', [
        'folder_id' => $folder->id
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'folder_id' => 'required|exists:folders,id',
        'files.*' => 'required|file|max:10240', 
    ]);

    foreach ($request->file('files') as $uploadedFile) {
        $path = $uploadedFile->store('uploads', 'public');

        File::create([
            'folder_id' => $request->folder_id,
            'user_id' => auth()->id(), 
            'title' => $uploadedFile->getClientOriginalName(),
            'description' => '', 
            'path' => $path,
        ]);
    }

    return redirect()->route('files.show', $request->folder_id)
                     ->with('success', 'Files uploaded successfully.');
}


    /**
     * Display the specified resource.
     */

public function show($id)
{
    $folder = Folder::with('files')->findOrFail($id);
    return view("pages.file-management.files", compact('folder'));
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
