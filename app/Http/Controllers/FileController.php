<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Storage; 

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
     $files= File::all();
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
        'files.*' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,bmp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt', 
    ], [
        'files.*.mimes' => 'Only image and document files are allowed.',
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

    // return redirect()->route('files.show', $request->folder_id)
    //                  ->with('success', 'Files uploaded successfully.');


                       $notification = notify('File has been Created successfully');
        return redirect()->route('files.show',$request->folder_id)->with($notification);
}



    /**
     * Display the specified resource.
     */



 public function show($id)
    {
        $folder = Folder::with(['files' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
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
    $file = File::findOrFail($id);

    
    Storage::disk('public')->delete($file->path);

    
    $file->delete();
    
                   $notification = notify('File deleted successfully');

                  return redirect()->back()->with($notification);
}


// download 

public function download(File $file)
{
    $path = storage_path('app/public/' . $file->path);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path, $file->title);
}

// view file 
public function view(File $file)
{
    return view('pages.file-management.view-file', compact('file'));
}

}