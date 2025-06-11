<?php

namespace App\Http\Controllers;

use App\DataTables\FolderDataTable;
use App\Models\MemberFolder;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FolderDataTable $dataTable )
    {
        return $dataTable->render("pages.file-management.index");
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');
        return User::where('firstname', 'like', "%{$q}%")
            ->select('id', 'firstname')
            ->limit(20)
            ->get();
    }

    public function preload(Request $request)
    {
        $ids = $request->get('ids', []);
        return User::whereIn('id', $ids)->select('id', 'firstname')->get();
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

        $folder = Folder::create([
            'name' => $request->name,
        ]);

        $currentUserId = auth()->id();

        MemberFolder::create([
            'folder_id' => $folder->id,
            'user_id' => $currentUserId,
            'is_owner' => true,
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
    public function edit( $id)
    {
        $folder = Folder::findOrFail($id);
        $users = User::whereNot('id',auth()->user()->id)->orderBy('firstname')->get();
        $folderMemberIds = $folder->members()->pluck('user_id')->toArray();
        return view('pages.file-management.edit-folder',compact(
            'users','folder','folderMemberIds'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Folder $folder)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'array',
            'members.*' => 'exists:users,id',
        ]);

        $folder->update(['name' => $request->name]);

        $folder->syncMembers($request->members ?? []);

        $notification = notify('Folder has been updated');
        return redirect()->route('folders.index')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);
        $isOwner = MemberFolder::where('folder_id', $folder->id)
            ->where('user_id', auth()->id())
            ->where('is_owner', true)
            ->exists();
        
        if (! $isOwner) {
            $notification = notify("You don't have enough priviledge to delete this folder!");
            return redirect()->back()->with($notification);
        }

        // delete members
        MemberFolder::where('folder_id', $folder->id)->delete();

        $folder->files()->each(fn ($file) => Storage::delete($file->path));
        $folder->files()->delete();

        $folder->delete();
        $notification = notify('Folder has been deleted');
        return redirect()->route('folders.index')->with('success', $notification);
    }
}
