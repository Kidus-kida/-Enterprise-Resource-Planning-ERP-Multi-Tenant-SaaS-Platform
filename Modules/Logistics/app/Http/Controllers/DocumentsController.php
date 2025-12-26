<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Logistics\Models\Document;
use Illuminate\Support\Facades\Storage;

use Modules\Logistics\Models\Shipment;
use Yajra\DataTables\Facades\DataTables;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Document::with('shipment')->select('documents.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('shipment_no', function($row){
                    return $row->shipment ? '<a href="'.route('logistics.shipments.show', $row->shipment_id).'">'.$row->shipment->shipment_no.'</a>' : 'N/A';
                })
                ->addColumn('file_size', function($row){
                    return round($row->file_size / 1024, 2) . ' KB';
                })
                ->addColumn('status', function($row){
                     $color = match($row->status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning'
                    };
                    return '<span class="badge bg-'.$color.'">'.ucfirst($row->status).'</span>';
                })
                 ->addColumn('action', function($row){
                    $btn = '<div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="'.route('logistics.documents.download', $row->id).'"><i class="fa fa-download m-r-5"></i> Download</a>
                                    '.($row->status == 'pending' ? '<a class="dropdown-item" href="'.route('logistics.documents.approve', $row->id).'"><i class="fa fa-check m-r-5"></i> Approve</a>' : '').'
                                    <a class="dropdown-item deleteBtn" href="#" data-route="'.route('logistics.documents.destroy', $row->id).'" data-id="'.$row->id.'" data-title="Delete Document" data-question="Are you sure you want to delete this document?"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                </div>
                            </div>';
                    return $btn;
                })
                ->rawColumns(['shipment_no', 'status', 'action'])
                ->make(true);
        }

        $totalDocuments = Document::count();
        $pendingDocuments = Document::where('status', 'pending')->count();
        $approvedDocuments = Document::where('status', 'approved')->count();

        return view('logistics::documents.index', compact('totalDocuments', 'pendingDocuments', 'approvedDocuments'));
    }

    public function create()
    {
        $shipments = Shipment::all();
        return view('logistics::documents.create', compact('shipments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'file' => 'required|file|max:10240', // 10MB
            'type' => 'required',
            'name' => 'required'
        ]);

        $file = $request->file('file');
        $path = $file->store('logistics/documents');

        Document::create([
            'shipment_id' => $request->shipment_id,
            'name' => $request->name,
            'type' => $request->type,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id() ?? 1, // Fallback if no auth
            'uploaded_at' => now(),
            'status' => 'pending',
            'notes' => $request->notes
        ]);

        return redirect()->route('logistics.documents.index')->with('success', 'Document uploaded successfully');
    }

    public function download(Document $document)
    {
        return Storage::download($document->file_path, $document->name);
    }
    
    public function approve(Document $document) 
    {
        $document->update(['status' => 'approved']);
        return back()->with('success', 'Document approved');
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        Storage::delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document deleted successfully');
    }
}
