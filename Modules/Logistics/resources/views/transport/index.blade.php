@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Transport & Delivery</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">Transport</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('logistics.transport.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Schedule Trip</a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-road"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $totalTrips }}</h3>
                        <span>Total Trips</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-truck"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $activeTrips }}</h3>
                        <span>Active Trips</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-exclamation-triangle"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $delayedTrips }}</h3>
                        <span>Delayed</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-check"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $completedTrips }}</h3>
                        <span>Completed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped custom-table" id="transport-table">
                    <thead>
                        <tr>
                            <th>Trip No</th>
                            <th>Container</th>
                            <th>Driver Info</th>
                            <th>Route</th>
                            <th>Plate No</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script type="module">
    $(document).ready(function() {
        if ($('#transport-table').length > 0) {
            $('#transport-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('logistics.transport.index') }}",
                columns: [
                    {data: 'trip_no', name: 'trip_no'},
                    {data: 'container_no', name: 'container_no'},
                    {data: 'driver_info', name: 'driver_name'}, 
                    {data: 'origin', name: 'origin', render: function(data, type, row) {
                        return row.origin + ' <i class="fa fa-arrow-right"></i> ' + row.destination;
                    }},
                    {data: 'vehicle_plate', name: 'vehicle_plate'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        }
    });
</script>
@endpush
@endsection
