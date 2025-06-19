<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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
        $files = File::all();
        return view("pages.file-management.files", compact('files'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        /// signed route
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

        $notification = notify('File has been created successfully');
        return redirect()->route('files.show', $request->folder_id)->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $folder = Folder::findOrFail($id);
        $files = File::where('folder_id', $id)->get();
        
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
        $file = File::findOrFail($id);
        Storage::disk('public')->delete($file->path);
        $file->delete();
        
        $notification = notify('File deleted successfully');
        return redirect()->back()->with($notification);
    }

    /**
     * Download the specified file.
     */
    public function download(File $file)
    {
        // signed route
        if (!request()->hasValidSignature()) {
            abort(403, 'Invalid or expired download link');
        }

        $path = storage_path('app/public/' . $file->path);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $file->title);
    }

    /**
     * View the specified file.
     */
    public function view(File $file)
    {
        //// signed route
        if (!request()->hasValidSignature()) {
            abort(403, 'Invalid or expired view link');
        }

        return view('pages.file-management.view-file', compact('file'));
    }
}