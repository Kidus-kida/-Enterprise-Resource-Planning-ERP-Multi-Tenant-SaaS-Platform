<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Logistics\Models\Shipment;
use Modules\Logistics\Models\DryPort;
use Yajra\DataTables\Facades\DataTables;

class ShipmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Shipment::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('logistics.shipments.show', $row->id).'" class="edit btn btn-primary btn-sm">View</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('logistics::shipments.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $dryPorts = DryPort::where('is_active', true)->get();
        return view('logistics::shipments.create', compact('dryPorts'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipment_no' => 'required|unique:shipments,shipment_no',
            'vendor' => 'required',
            'vendor_country' => 'required',
            'port_of_loading' => 'required',
            'port_of_discharge' => 'required',
            'expected_arrival' => 'required|date',
        ]);

        $shipment = new Shipment($request->all());
        $shipment->user_id = auth()->id();
        $shipment->save();

        return redirect()->route('logistics.shipments.index')
                        ->with('success','Shipment created successfully.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $shipment = Shipment::with(['containers', 'documents', 'customsDeclaration', 'dryPort'])->findOrFail($id);
        return view('logistics::shipments.show', compact('shipment'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $shipment = Shipment::findOrFail($id);
        $dryPorts = DryPort::where('is_active', true)->get();
        return view('logistics::shipments.edit', compact('shipment', 'dryPorts'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
         $request->validate([
            'vendor' => 'required',
            'vendor_country' => 'required',
            'port_of_loading' => 'required',
            'port_of_discharge' => 'required',
            'expected_arrival' => 'required|date',
        ]);

        $shipment = Shipment::findOrFail($id);
        $shipment->update($request->all());

        return redirect()->route('logistics.shipments.index')
                        ->with('success','Shipment updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $shipment = Shipment::findOrFail($id);
        $shipment->delete();

        return redirect()->route('logistics.shipments.index')
                        ->with('success','Shipment deleted successfully');
    }
}
