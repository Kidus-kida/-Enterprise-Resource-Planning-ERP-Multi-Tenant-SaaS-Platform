<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Logistics\Models\Container;
use Modules\Logistics\Models\Shipment;
use Yajra\DataTables\Facades\DataTables;

class ContainersController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Container::with('shipment')->select('containers.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('shipment_no', function($row){
                    return $row->shipment ? '<a href="'.route('logistics.shipments.show', $row->shipment_id).'">'.$row->shipment->shipment_no.'</a>' : 'N/A';
                })
                ->addColumn('status', function($row){
                    return ucfirst(str_replace('_', ' ', $row->status));
                })
                ->addColumn('action', function($row){
                   $btn = '<div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="'.route('logistics.containers.edit', $row->id).'"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                    <a class="dropdown-item deleteBtn" href="#" data-route="'.route('logistics.containers.destroy', $row->id).'" data-id="'.$row->id.'" data-title="Delete Container" data-question="Are you sure you want to delete this container?"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                </div>
                            </div>';
                   return $btn;
                })
                ->rawColumns(['shipment_no', 'action'])
                ->make(true);
        }
        
        $totalContainers = Container::count();
        $inTransit = Container::where('status', 'in_transit')->count();
        $atDjibouti = Container::where('status', 'at_djibouti')->count();
        $atDryPort = Container::where('status', 'at_dry_port')->count();
        $demurrageRisk = Container::where('demurrage_days', '>', 0)->count();

        return view('logistics::containers.index', compact('totalContainers', 'inTransit', 'atDjibouti', 'atDryPort', 'demurrageRisk'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $shipments = Shipment::all();
        return view('logistics::containers.create', compact('shipments'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'container_no' => 'required|unique:containers,container_no',
            'size' => 'required',
            'type' => 'required',
        ]);

        Container::create($request->all());

        return redirect()->route('logistics.containers.index')->with('success', 'Container added successfully');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $container = Container::findOrFail($id);
        $shipments = Shipment::all();
        return view('logistics::containers.edit', compact('container', 'shipments'));
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
            'shipment_id' => 'required|exists:shipments,id',
            'container_no' => 'required|unique:containers,container_no,'.$id,
            'size' => 'required',
            'type' => 'required',
        ]);

        $container = Container::findOrFail($id);
        $container->update($request->all());

        return redirect()->route('logistics.containers.index')->with('success', 'Container updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $container = Container::findOrFail($id);
        $container->delete();

        return redirect()->route('logistics.containers.index')->with('success', 'Container deleted successfully');
    }
}
