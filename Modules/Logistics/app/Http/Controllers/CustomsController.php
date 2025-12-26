<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Logistics\Models\CustomsDeclaration;
use Modules\Logistics\Models\Shipment;
use Modules\Logistics\Models\HSCode;
use Yajra\DataTables\Facades\DataTables;

class CustomsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CustomsDeclaration::with(['shipment', 'hsCode'])->select('customs_declarations.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('shipment_no', function($row){
                    return $row->shipment ? '<a href="'.route('logistics.shipments.show', $row->shipment_id).'">'.$row->shipment->shipment_no.'</a>' : 'N/A';
                })
                ->addColumn('status', function($row){
                     return ucfirst($row->status);
                })
                ->addColumn('risk_channel', function($row){
                    $color = match($row->risk_channel) {
                        'green' => 'success',
                        'yellow' => 'warning',
                        'red' => 'danger',
                        default => 'secondary'
                    };
                    return '<span class="badge bg-'.$color.'">'.ucfirst($row->risk_channel).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="'.route('logistics.customs.show', $row->id).'"><i class="fa fa-eye m-r-5"></i> View</a>
                                    <a class="dropdown-item" href="'.route('logistics.customs.edit', $row->id).'"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                    <a class="dropdown-item deleteBtn" href="#" data-route="'.route('logistics.customs.destroy', $row->id).'" data-id="'.$row->id.'" data-title="Delete Declaration" data-question="Are you sure you want to delete this declaration?"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                </div>
                            </div>';
                    return $btn;
                })
                ->rawColumns(['shipment_no', 'risk_channel', 'action'])
                ->make(true);
        }

        $totalDeclarations = CustomsDeclaration::count();
        $pending = CustomsDeclaration::where('status', 'draft')->orWhere('status', 'submitted')->count();
        $cleared = CustomsDeclaration::where('status', 'released')->count();
        $totalDutyPaid = CustomsDeclaration::sum('total_duties');

        return view('logistics::customs.index', compact('totalDeclarations', 'pending', 'cleared', 'totalDutyPaid'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $shipmentId = $request->query('shipment_id');
        $selectedShipment = null;
        if($shipmentId) {
            $selectedShipment = Shipment::find($shipmentId);
        }

        $shipments = Shipment::whereDoesntHave('customsDeclaration')->get();
        // If selected shipment is already in the list (good), if not (already declared?), handle it.
        // For simplicity, we just pass the full list + selected ID.
        
        $hsCodes = HSCode::where('is_active', true)->get();
        return view('logistics::customs.create', compact('shipments', 'hsCodes', 'selectedShipment'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id|unique:customs_declarations,shipment_id',
            'declaration_no' => 'required|unique:customs_declarations,declaration_no',
            'hs_code_id' => 'required|exists:hs_codes,id',
            'cif_value_usd' => 'required|numeric',
            'exchange_rate' => 'required|numeric',
            'declaration_date' => 'required|date',
        ]);
        
        $declaration = new CustomsDeclaration($request->all());
        
        // Auto-calculate ETB values if not provided (simplified logic)
        $declaration->cif_value_etb = $declaration->cif_value_usd * $declaration->exchange_rate;
        
        // In a real app, we would calculate duties here or expect them from form.
        // For now we trust the form inputs or set defaults.
        
        $declaration->save();

        return redirect()->route('logistics.customs.index')->with('success', 'Declaration created successfully');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $declaration = CustomsDeclaration::with(['shipment', 'hsCode'])->findOrFail($id);
        return view('logistics::customs.show', compact('declaration'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $declaration = CustomsDeclaration::findOrFail($id);
        $shipments = Shipment::all(); 
        $hsCodes = HSCode::where('is_active', true)->get();
        return view('logistics::customs.edit', compact('declaration', 'shipments', 'hsCodes'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $declaration = CustomsDeclaration::findOrFail($id);
        $declaration->update($request->all());
        return redirect()->route('logistics.customs.index')->with('success', 'Declaration updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $declaration = CustomsDeclaration::findOrFail($id);
        $declaration->delete();
        return redirect()->route('logistics.customs.index')->with('success', 'Declaration deleted successfully');
    }
}
