<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Logistics\Models\TransportTrip;
use Modules\Logistics\Models\Container;
use Yajra\DataTables\Facades\DataTables;

class TransportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TransportTrip::with('container')->select('transport_trips.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('container_no', function($row){
                    return $row->container ? '<a href="'.route('logistics.containers.edit', $row->container_id).'">'.$row->container->container_no.'</a>' : 'N/A';
                })
                ->addColumn('driver_info', function($row){
                    return $row->driver_name . '<br><small>'.$row->driver_phone.'</small>';
                })
                ->addColumn('status', function($row){
                     $color = match($row->status) {
                        'completed' => 'success',
                        'delayed' => 'danger',
                        'in_transit' => 'warning',
                        'loading' => 'info',
                        default => 'secondary'
                    };
                    return '<span class="badge bg-'.$color.'">'.ucfirst(str_replace('_', ' ', $row->status)).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="'.route('logistics.transport.show', $row->id).'"><i class="fa fa-eye m-r-5"></i> View</a>
                                    <a class="dropdown-item" href="'.route('logistics.transport.edit', $row->id).'"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                    <a class="dropdown-item deleteBtn" href="#" data-route="'.route('logistics.transport.destroy', $row->id).'" data-id="'.$row->id.'" data-title="Delete Trip" data-question="Are you sure you want to delete this trip?"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                </div>
                            </div>';
                    return $btn;
                })
                ->rawColumns(['container_no', 'driver_info', 'status', 'action'])
                ->make(true);
        }
        
        $totalTrips = TransportTrip::count();
        $activeTrips = TransportTrip::whereIn('status', ['loading', 'in_transit'])->count();
        $delayedTrips = TransportTrip::where('status', 'delayed')->count();
        $completedTrips = TransportTrip::where('status', 'completed')->count();

        return view('logistics::transport.index', compact('totalTrips', 'activeTrips', 'delayedTrips', 'completedTrips'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $containers = Container::whereDoesntHave('transportTrips', function($q) {
            $q->whereIn('status', ['scheduled', 'loading', 'in_transit']);
        })->get();
        
        return view('logistics::transport.create', compact('containers'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'trip_no' => 'required|unique:transport_trips',
            'container_id' => 'required|exists:containers,id',
            'origin' => 'required',
            'destination' => 'required',
            'vehicle_plate' => 'required',
            'driver_name' => 'required',
        ]);

        TransportTrip::create($request->all());

        return redirect()->route('logistics.transport.index')->with('success', 'Trip scheduled successfully');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $trip = TransportTrip::with('container')->findOrFail($id);
        return view('logistics::transport.show', compact('trip'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $trip = TransportTrip::findOrFail($id);
        $containers = Container::all(); // Allow changing container if needed? Or just show current.
        return view('logistics::transport.edit', compact('trip', 'containers'));
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
            'container_id' => 'required|exists:containers,id',
            'trip_no' => 'required|unique:transport_trips,trip_no,'.$id,
            'origin' => 'required',
            'destination' => 'required',
            'vehicle_plate' => 'required',
            'driver_name' => 'required',
        ]);

        $trip = TransportTrip::findOrFail($id);
        $trip->update($request->all());
        return redirect()->route('logistics.transport.index')->with('success', 'Trip updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $trip = TransportTrip::findOrFail($id);
        $trip->delete();
        return redirect()->route('logistics.transport.index')->with('success', 'Trip deleted successfully');
    }
}
